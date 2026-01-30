<?php

namespace database\seeders;

use App\Domain\Models\Software;
use App\Domain\Models\SoftwareVersion;
use App\Domain\Models\SoftwareSlug;
use App\Domain\Services\ServiceApi\GiteaApiServiceInterface;
use Artisan;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SoftwareTableSeeder extends Seeder
{
    protected $giteaService;
    protected $phpVersions;
    const DEFAULT_PHP_VERSION = '8.0';
    const PHP_VERSION_REPO_URL = 'https://git.autotech.se/LMS-Customer/moodle-baseline';
    const PHP_VERSION_REPO_BRANCH = 'develop';
    const PHP_VERSION_REPO_FILE = 'core/moodle.yaml';

    public function __construct()
    {
        $this->phpVersions = [];
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Atomic lock to prevent overlap; no "stuck" state if the process dies.
        $lock = Cache::lock('softwareSyncLock', 300);

        if (!$lock->get()) {
            Log::warning('Software sync already running (lock not acquired)');
            return;
        }

        try {
            Cache::put('softwareSync', 'running', now()->addMinutes(60));
            $this->giteaService = app(GiteaApiServiceInterface::class);
            $this->setPhpVersions();

            // Ensure version types exist.
            DB::table('git_version_types')->updateOrInsert(
                ['name' => 'branch'],
                ['created_at' => now(), 'updated_at' => now()],
            );
            $branchTypeId = (int) DB::table('git_version_types')->where('name', 'branch')->value('id');

            DB::table('git_version_types')->updateOrInsert(
                ['name' => 'tag'],
                ['created_at' => now(), 'updated_at' => now()],
            );

            // Slugs
            $moodleSlugId = (int) (SoftwareSlug::where('value', 'moodle')->value('id') ?? 0);
            $moodleWorkplaceSlugId = (int) (SoftwareSlug::where('value', 'moodle_workplace')->value('id') ?? 0);

            // Seed softwares
            $data = [
                [
                    'name' => 'Moodle',
                    'slug' => 'moodle',
                    'software_slug' => $moodleSlugId,
                    'git_url' => 'https://git.autotech.se/LMS-Mirror/moodle',
                    'version_supported' => 'main',
                    'git_version_type_id' => $branchTypeId,
                ],
                [
                    'name' => 'Moodle Workplace',
                    'slug' => 'moodle_workplace',
                    'software_slug' => $moodleWorkplaceSlugId,
                    'git_url' => 'https://git.autotech.se/base-products/moodle-workplace',
                    'version_supported' => 'WORKPLACE_ROLLING_500_2',
                    'git_version_type_id' => $branchTypeId,
                ],
            ];

            // Upsert + deleted_at handling
            foreach ($data as $obj) {
                $url = $obj['git_url'];
                $isDelete = (bool) ($obj['delete'] ?? false);

                $updateData = [
                    'version_supported' => $obj['version_supported'],
                    'git_version_type_id' => $obj['git_version_type_id'],
                    'slug' => $obj['slug'],
                    'software_slug' => $obj['software_slug'] ?? null,
                    'updated_at' => now(),
                ];

                Software::updateOrInsert(
                    ['name' => $obj['name'], 'git_url' => $url],
                    $updateData,
                );

                $softwareRecord = Software::where('name', $obj['name'])->where('git_url', $url)->first();
                if ($softwareRecord) {
                    if ($isDelete && !$softwareRecord->deleted_at) {
                        $softwareRecord->deleted_at = now();
                        $softwareRecord->save();
                        Log::info("Ignoring software as flagged for deletion", ['name' => $obj['name']]);
                        continue;
                    } elseif (!$isDelete && $softwareRecord->deleted_at) {
                        $softwareRecord->deleted_at = null;
                        $softwareRecord->save();
                    }
                }
            }

            // Fix missing slug / software_slug on ALL software
            $allSoftware = Software::get();

            foreach ($allSoftware as $software) {
                $save = false;

                if (empty($software->slug)) {
                    $save = true;
                    $software->slug = strtolower(str_replace(' ', '_', $software->name));
                }

                if (empty($software->software_slug)) {
                    $save = true;

                    $parts = explode('/', rtrim((string) $software->git_url, '/'));
                    $softwareSlugValue = str_replace('.git', '', (string) end($parts));
                    $softwareSlugValue = str_replace('-', '_', strtolower($softwareSlugValue));

                    $softwareSlugId = (int) (SoftwareSlug::where('value', $softwareSlugValue)->value('id') ?? 0);
                    $software->software_slug = $softwareSlugId;
                }

                if ($save) {
                    $software->updated_at = now();
                    $software->save();
                }
            }

            // Reload software (with relation)
            $allSoftware = Software::with('softwareSlug')->get();

            foreach ($allSoftware as $software) {
                if (empty($software->software_slug) || !$software->softwareSlug) {
                    Log::warning("software_slug not set; skipping", ['software' => $software->name, 'id' => $software->id]);
                    continue;
                }

                $softwareSlugValue = (string) $software->softwareSlug->value;
                $url = (string) $software->git_url;
                $id = (int) $software->id;

                try {
                    $branches = $this->giteaService->versionsAvailable($url, GIT_VERSION_TYPE_BRANCH);
                    $tags = $this->giteaService->versionsAvailable($url, GIT_VERSION_TYPE_TAG);

                    Log::info("Fetched refs", [
                        'software' => $software->name,
                        'repo' => $url,
                        'branches_count' => is_array($branches) ? count($branches) : null,
                        'tags_count' => is_array($tags) ? count($tags) : null,
                    ]);

                    switch ($softwareSlugValue) {
                        case 'moodle':
                        case 'moodle_workplace':

                            foreach ($branches as $branch) {
                                $ref = $branch['name'] ?? null;
                                if (!$ref) {
                                    continue;
                                }

                                try {
                                    $decodedContent = $this->fetchVersionPhpDecoded($url, $ref, $softwareSlugValue);

                                    $versionIdString = $this->extractSomething($decodedContent, '$version');
                                    $versionId = (int) preg_replace('/\D/', '', (string) $versionIdString);

                                    $branchString = $this->extractSomething($decodedContent, '$branch');
                                    $branchString = $this->sanitizeBranchString((string) $branchString);

                                    $releaseString = $this->extractSomething($decodedContent, '$release');
                                    $releaseParts = $this->parseReleaseString((string) $releaseString);

                                    // IMPORTANT FIX: do NOT include version_id in the match key.
                                    SoftwareVersion::updateOrInsert(
                                        [
                                            'software_id' => $id,
                                            'version_name' => $ref,
                                            'version_type' => 1,
                                        ],
                                        [
                                            'version_id' => $versionId,
                                            'php_version' => $this->getPhpVersionForMoodle($ref, $ref, $software->name),
                                            'major_version' => $releaseParts['major'],
                                            'minor_version' => $releaseParts['minor'],
                                            'patch_version' => $releaseParts['patch'],
                                            'prefix' => $releaseParts['prefix'],
                                            'branch_version' => $branchString,
                                            'updated_at' => now(),
                                        ],
                                    );

                                    Log::info("Updated branch version", [
                                        'software' => $software->name,
                                        'ref' => $ref,
                                        'version_id' => $versionId,
                                        'major' => $releaseParts['major'],
                                        'minor' => $releaseParts['minor'],
                                        'patch' => $releaseParts['patch'],
                                    ]);
                                } catch (\Throwable $e) {
                                    Log::error("Error processing branch", [
                                        'software' => $software->name,
                                        'ref' => $ref,
                                        'repo' => $url,
                                        'error' => $e->getMessage(),
                                    ]);
                                    Log::error($e->getTraceAsString());
                                    continue;
                                }
                            }

                            foreach ($tags as $tag) {
                                $ref = $tag['name'] ?? null;
                                if (!$ref) {
                                    continue;
                                }

                                try {
                                    $decodedContent = $this->fetchVersionPhpDecoded($url, $ref, $softwareSlugValue);

                                    $versionIdString = $this->extractSomething($decodedContent, '$version');
                                    $versionId = (int) preg_replace('/\D/', '', (string) $versionIdString);

                                    $branchString = $this->extractSomething($decodedContent, '$branch');
                                    $branchString = $this->sanitizeBranchString((string) $branchString);

                                    $releaseString = $this->extractSomething($decodedContent, '$release');
                                    $releaseParts = $this->parseReleaseString((string) $releaseString);

                                    // IMPORTANT FIX: do NOT include version_id in the match key.
                                    SoftwareVersion::updateOrInsert(
                                        [
                                            'software_id' => $id,
                                            'version_name' => $ref,
                                            'version_type' => 2,
                                        ],
                                        [
                                            'version_id' => $versionId,
                                            'php_version' => $this->getPhpVersionForMoodle($ref, $ref, $software->name),
                                            'major_version' => $releaseParts['major'],
                                            'minor_version' => $releaseParts['minor'],
                                            'patch_version' => $releaseParts['patch'],
                                            'prefix' => $releaseParts['prefix'],
                                            'branch_version' => $branchString,
                                            'updated_at' => now(),
                                        ],
                                    );

                                    Log::info("Updated tag version", [
                                        'software' => $software->name,
                                        'ref' => $ref,
                                        'version_id' => $versionId,
                                    ]);
                                } catch (\Throwable $e) {
                                    Log::error("Error processing tag", [
                                        'software' => $software->name,
                                        'ref' => $ref,
                                        'repo' => $url,
                                        'error' => $e->getMessage(),
                                    ]);
                                    Log::error($e->getTraceAsString());
                                    continue;
                                }
                            }

                            break;

                        default:
                            break;
                    }
                } catch (\Throwable $e) {
                    Log::error("Error fetching refs from repo", [
                        'software' => $software->name,
                        'repo' => $url,
                        'error' => $e->getMessage(),
                    ]);
                    continue;
                }
            }
        } finally {
            Cache::forget('softwareSync');
            optional($lock)->release();
        }
    }

    private function fetchVersionPhpDecoded(string $repoUrl, string $ref, string $softwareSlugValue): string
    {
        // Moodle is usually root/version.php
        // Workplace is often moodle/version.php (repo contains moodle in a subdir)
        $paths = ($softwareSlugValue === 'moodle_workplace')
            ? ['version.php', 'moodle/version.php', 'src/version.php', 'server/version.php']
            : ['version.php', 'moodle/version.php'];

        $lastError = null;

        foreach ($paths as $path) {
            try {
                $content = $this->giteaService->getContent($repoUrl, $path, $ref);

                if (!empty($content['content'])) {
                    Log::info("Fetched version file", [
                        'repo' => $repoUrl,
                        'ref' => $ref,
                        'path' => $path,
                    ]);

                    $decoded = base64_decode((string) $content['content'], true);

                    if ($decoded === false || $decoded === '') {
                        throw new \RuntimeException("Base64 decode failed or empty for path={$path}, ref={$ref}");
                    }

                    return $decoded;
                }
            } catch (\Throwable $e) {
                $lastError = $e;

                // If it's a 404, try next path. Anything else: rethrow.
                $code = (int) $e->getCode();
                if ($code !== 404) {
                    throw $e;
                }
            }
        }

        $msg = "version file not found. tried paths=[" . implode(', ', $paths) . "], ref={$ref}, repo={$repoUrl}";
        if ($lastError) {
            $msg .= " last_error=" . $lastError->getMessage();
        }

        throw new \RuntimeException($msg);
    }


    private function parseReleaseString(string $raw): array
    {
        $result = [
            'major' => '',
            'minor' => '',
            'patch' => '',
            'prefix' => '',
        ];

        // 1) Remove inline comments.
        //    - strips // ... to end of line
        //    - strips /* ... */ blocks
        $clean = preg_replace('~//.*$~m', '', $raw);
        $clean = preg_replace('~/\*.*?\*/~s', '', $clean);
        $clean = trim((string) $clean);

        // 2) If it's an assignment line, keep only RHS after '='.
        if (strpos($clean, '=') !== false) {
            $clean = trim(substr($clean, strpos($clean, '=') + 1));
        }

        // 3) Remove trailing semicolon(s).
        $clean = rtrim($clean, " \t\n\r\0\x0B;");

        // 4) Remove wrapping quotes (single or double), even if only one side exists.
        $clean = trim($clean);
        if (
            (strlen($clean) >= 2) &&
            (($clean[0] === "'" && substr($clean, -1) === "'") || ($clean[0] === '"' && substr($clean, -1) === '"'))
        ) {
            $clean = substr($clean, 1, -1);
        } else {
            $clean = trim($clean, " \t\n\r\0\x0B'\"");
        }

        $clean = trim($clean);

        // 5) Parse: optional prefix + major.minor.patch(where patch includes everything after 2nd dot)
        if (preg_match('/^([a-zA-Z]*)(\d+)\.(\d+)\.(.+)$/', $clean, $m)) {
            $result['prefix'] = $m[1] ?? '';
            $result['major'] = $m[2] ?? '';
            $result['minor'] = $m[3] ?? '';
            $result['patch'] = trim($m[4] ?? '');
        }

        return $result;
    }


    private function sanitizeBranchString(string $raw): string
    {
        // Trim whitespace first
        $raw = trim($raw);

        // Extract only leading digits
        if (preg_match('/^\d+/', $raw, $matches)) {
            return $matches[0];
        }

        return '';
    }


    public function extractSomething($decodedContent, $something)
    {
        $extraction = '';

        // Find the "release" key in the PHP serialized data
        // Match any key-value pair in the format $variable = 'value'
        $lines = explode("\n", $decodedContent);

        $releaseContent = '';

        foreach ($lines as $line) {
            if (strpos($line, $something) !== false) {
                $releaseContent = $line;
            }
        }

        $equalsPosition = strpos($releaseContent, '=');

        // If '=' is found, extract the version part
        if ($equalsPosition !== false) {
            $extractionStart = $equalsPosition + 1;
            $extractionString = trim(substr($releaseContent, $extractionStart), " ';\n\r");
            $extraction = $extractionString ?: 'Unknown';
        }

        return $extraction;
    }

    private function setPhpVersions()
    {
        try {
            $content = $this->giteaService->getContent(self::PHP_VERSION_REPO_URL, self::PHP_VERSION_REPO_FILE, self::PHP_VERSION_REPO_BRANCH);
            $lines = explode("\n", base64_decode($content['content']));

            $sectionStart = false;
            $currentMoodleVersion = '';

            foreach ($lines as $line) {
                $line = trim($line);
                if ($line == 'moodle:') {
                    $sectionStart = true;
                    $currentMoodleVersion = '';
                    continue;
                }

                if ($sectionStart) {
                    if (str_starts_with($line, 'version:')) {
                        $currentMoodleVersion = trim(str_replace('version:', '', $line));
                    } elseif (str_starts_with($line, 'major:')) {
                        $currentMoodleVersion = trim(str_replace('major:', '', $line));
                    } elseif (str_starts_with($line, 'minor:')) {
                        $currentMoodleVersion = $currentMoodleVersion . '.' . trim(str_replace('minor:', '', $line));
                    } elseif (str_starts_with($line, 'patch:')) {
                        $currentMoodleVersion = $currentMoodleVersion . '.' . trim(str_replace('patch:', '', $line));
                    } elseif (str_starts_with($line, 'php:')) {
                        $this->phpVersions[$currentMoodleVersion] = trim(str_replace('php:', '', $line));
                        $sectionStart = false;
                        $currentMoodleVersion = '';
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error("Error setting PHP versions. error: {$e->getMessage()}");
        }
    }

    private function normalizeVersion($version)
    {
        $pattern = '/v(\d+\.\d+(\.\d+)?)/';

        if (preg_match($pattern, $version, $matches)) {
            $version = $matches[1];
        }
        // Ensure the version has a three-part format for accurate comparison
        $parts = explode('.', $version);
        $parts = array_pad($parts, 3, '0'); // Pad to three parts
        return implode('.', $parts);
    }

    private function getPhpVersionForMoodle($inputVersion, $branch = '', $software = '')
    {
        // Initialize default PHP version
        $phpVersion = self::DEFAULT_PHP_VERSION;

        try {
            if (str_starts_with($inputVersion, 'v') || strpos($inputVersion, '.') !== false) {
                // Convert version strings to comparable numbers (e.g., 4.0 to 4.0.0)
                $inputVersionNorm = $this->normalizeVersion($inputVersion);
                // Sort versions in descending order to easily find the nearest smaller or equal version
                krsort($this->phpVersions);

                foreach ($this->phpVersions as $softwareVersion => $phpVersionCandidate) {
                    // Normalize the software version for comparison
                    $softwareVersion = $this->normalizeVersion($softwareVersion);

                    // Check if the input version is less than or equal to the software version
                    if (version_compare($inputVersionNorm, $softwareVersion, '>=')) {
                        $phpVersion = $phpVersionCandidate;
                        break;
                    }
                }
            } elseif (isset($this->phpVersions[$inputVersion])) {
                $phpVersion = $this->phpVersions[$inputVersion];
            }

            Log::info("Picked PHP version $inputVersion => $phpVersion for $branch of $software");

            return $phpVersion;
        } catch (\Throwable $e) {
            Log::error("Error finding PHP version for $inputVersion. error: {$e->getMessage()}");
            return self::DEFAULT_PHP_VERSION;
        }
    }
}
