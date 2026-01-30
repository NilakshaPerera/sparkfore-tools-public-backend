<?php

namespace App\Domain\Services\Software;

use App\Domain\DataClasses\Software\Software;
use App\Domain\Jobs\SyncSoftwaresJob;
use App\Domain\Repositories\Software\SoftwareRepositoryInterface;
use App\Domain\Services\ServiceApi\GiteaApiServiceInterface;
use Cache;
use Illuminate\Pagination\LengthAwarePaginator;
use Avency\Gitea\Client;
use Log;

class SoftwareService implements SoftwareServiceInterface
{
    public const OWNER = 'owner';
    public const REPO = 'repo';
    private $client;
    private $gitUrl;
    private $authToken;

    public function __construct(
        private SoftwareRepositoryInterface $softwareRepository,
        private GiteaApiServiceInterface $giteaApiService
        )
    {
        $this->gitUrl = config('sparkfore.git_url');
        $this->authToken = config('sparkfore.git_auth_token');
        // Create open client for communication
        $this->createClient();
    }


    private function getParamsFromUrl($url, $param)
    {
        $path = parse_url($url, PHP_URL_PATH);
        $parts = self::cleanParts($path);
        if ($param === self::OWNER) {
            return $parts[0] ?? '';
        } elseif ($param === self::REPO) {
            return $parts[1] ?? '';
        }

        return '';

    }

    private static function cleanParts($path)
    {
        $parts = explode('/', $path);
        $cleaned = [];
        foreach ($parts as $part) {
            if (!empty(trim($part))) {
                $cleaned[] = trim($part);
            }
        }
        return $cleaned;
    }

    public function createClient()
    {
        $this->client = new Client(
            $this->gitUrl,
            [
                'type' => Client::AUTH_TOKEN,
                'auth' => $this->authToken
            ]
        );
    }

    public function listSoftware($params)
    {
        $params['with'] = [];

        $paginatedResult = $this->softwareRepository->listSoftware($params);

        $tableData = ($paginatedResult instanceof LengthAwarePaginator) ? $paginatedResult->through(function ($software) {
            return [
                'id' => $software->id,
                'name' => $software->name,
                'git_url' => $software->git_url,
                'supported_versions' => $software->version_supported
            ];
        }) : [];

        if (Cache::has("softwareSync") && Cache::get("softwareSync") == "running") {
            $return['extra']['sync_status'] = "running";
        } else {
            $return['extra']['sync_status'] = "idle";
        }
        Log::info("Software list", [Cache::get("softwareSync")]);

        $return["tableData"] = $tableData;
        return $return;
    }

    /**
     * @return mixed
     */
    public function getFormCreate()
    {
        return [
            'existing_software_slugs' => $this->softwareRepository->getAllExistingSlugs(),
            'software_slugs' => $this->softwareRepository->getAllSoftwareSlugs(),
            'types' => $this->softwareRepository->getFormCreate(),
            'base_git_url' => config('sparkfore.git_url')
        ];
    }

    /**
     * @param $params
     * @return mixed
     */
    public function storeSoftware($params)
    {
        $software = (new Software())
            ->setName($params['name'])
            ->setSlug($params['name_slug'] )
            ->setSoftwareSlugByValue($params['software_slug_value'] )
            ->setGitUrl($params['git_url'])
            ->setGitVersionTypeId($params['git_version_type_id'])
            ->setVersionSupported($params['version_supported']);
        return $this->softwareRepository->storeSoftware(array_filter($software->toArray()));
    }


    /**
     * @param $params
     * @return mixed
     */
    public function getSoftwareVersions($gitUrl, $typeId)
    {
        $owner = $this->getParamsFromUrl($gitUrl, self::OWNER);
        $repo = $this->getParamsFromUrl($gitUrl, self::REPO);
        if ($owner && $repo) {
            try {

                $result = false;

                if ($typeId == GIT_VERSION_TYPE_ID_BRANCH) {
                    $result = $this->client->repositories()->getBranches($owner, $repo);
                } elseif ($typeId == GIT_VERSION_TYPE_ID_TAG) {
                    $result = $this->client->repositories()->getTags($owner, $repo);
                }

                Log::info("Software versions from git", [$result]);

                return $result ;

            } catch (\Exception $e) {
                Log::error("Error getting software versions", [$e->getMessage(), $e]);
            }
        }
        return false;
    }

    /**
     * @param $params
     * @return mixed
     */
    public function updateSoftware($params)
    {
        $software = (new Software())
            ->setId($params['id'])
            ->setName($params['name'])
            ->setSlug($params['name_slug'] )
            ->setSoftwareSlugByValue($params['software_slug_value'] )
            ->setGitUrl($params['git_url'])
            ->setVersionSupported($params['version_supported'])
            ->setGitVersionTypeId($params['git_version_type_id']);
        return $this->softwareRepository->updateSoftware(array_filter($software->toArray()));
    }

    /**
     * @param $id
     * @return mixed
     */
    public function edit($id)
    {
        return $this->softwareRepository->edit($id);
    }

    public function update($id)
    {
        return $this->softwareRepository->delete($id);
    }

    public function syncSoftwares()
    {
        if (Cache::has("softwareSync") && Cache::get("softwareSync") == "running") {
            return "Software sync is already in progress";
        } else {
            Cache::put("softwareSync", "running", now()->addMinutes(60));
            SyncSoftwaresJob::dispatch()->onQueue('pluginsSync');
            return "Software sync job send to the queue";
        }
    }
}
