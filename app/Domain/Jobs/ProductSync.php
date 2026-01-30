<?php

namespace App\Domain\Jobs;

use App\Domain\Models\Plugin;
use App\Domain\Models\Product;
use App\Domain\Models\ProductHasPlugin;
use App\Domain\Services\Product\ProductServiceInterface;
use App\Domain\Services\ServiceApi\GiteaApiServiceInterface;
use App\Domain\Traits\GitProductPackage;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;
use Illuminate\Support\Str;
use Symfony\Component\Yaml\Yaml;
use Illuminate\Support\Facades\Cache;

class ProductSync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, GitProductPackage;

    protected $repoUrl;
    protected $productModel;
    protected $giteaApiService;
    protected $productService;
    protected $remainingDbProPlugins;
    protected $branches; // "develop", "staging", "master"
    protected $jobId;
    protected $repoObj;
    /**
     * Create a new job instance.
     */
    public function __construct($repoUrl, $branches = null, $repoObj = null)
    {
        $this->repoUrl = $repoUrl;
        $this->repoObj = $repoObj;
        $this->jobId = "ProductSync_" . hash('sha256', $repoUrl);
        $this->branches = ["develop", "staging", "master"];
        if ($branches && is_array($branches)) {
            $this->branches = $branches;
        }
    }

    public $tries = 2;

    public function failed(Exception $exception)
    {
        Cache::put($this->jobId, 'completed', 3600);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        if (!Cache::has($this->jobId)) {
            Cache::put($this->jobId, 'started', 3600);
        }
        $this->giteaApiService = app(GiteaApiServiceInterface::class);
        $this->productService = app(ProductServiceInterface::class);
        if ($this->checkRepoHasPlugins()) {
            if ($this->productModel) {
                $this->syncPluginsInBranches();
            } elseif ($this->repoObj) {
                Log::info("Database product is not available for $this->repoUrl. Creating product.");
                $this->createProductFromGit();
            }
        }
        Cache::put($this->jobId, 'completed', 3600);
    }

    private function syncPluginsInBranches()
    {
        $branches = $this->giteaApiService->versionsAvailable($this->repoUrl, GIT_VERSION_TYPE_BRANCH);
        foreach ($branches as $branch) {
            if (in_array($branch['name'], $this->branches)) {
                $this->syncPluginsInBranch($branch);
            }
        }
    }

    private function syncPluginsInBranch($branch)
    {
        Log::info("Processing branch", [$branch['name'], $this->repoUrl]);
        $repoTree = $this->giteaApiService->getContents($this->repoUrl, $branch['name']);
        $dbEnv = $this->getDbEnv($branch['name']);

        foreach ($repoTree as $repoTreeItem) {
            // Check if the item is a directory and matches the folder name
            if ($repoTreeItem["type"] === 'dir' && $repoTreeItem["name"] === "plugins") {
                $pluginsTree = $this->giteaApiService->getContent($this->repoUrl, "plugins", $branch['name']);
                foreach ($pluginsTree as $pluginsTreeItem) {
                    if ($pluginsTreeItem["type"] === 'file' && str_ends_with($pluginsTreeItem["name"], ".yaml")) {
                        $this->syncProductPlugins(
                            $pluginsTreeItem["name"],
                            $pluginsTreeItem["path"],
                            $branch['name'],
                            $dbEnv
                        );
                    }
                }
            }
        }
    }

    private function checkRepoHasPlugins()
    {

        $repoContent = $this->giteaApiService->getContents($this->repoUrl);

        foreach ($repoContent as $object) {
            $nameWithoutExtension = Str::lower(pathinfo($object['name'], PATHINFO_FILENAME));
            if ("plugins" === $nameWithoutExtension) {
                Log::info("$this->repoUrl has plugins in Git");
                $this->productModel = Product::where("git_url", $this->repoUrl)->with("productPlugins.plugin")->first();
                return true;
            }
        }
        Log::info("$this->repoUrl does NOT have plugins");
        return false;
    }

    private function getDbEnv($branch): string
    {
        $dbEnv = $branch;
        if ($branch == "develop") {
            $dbEnv = "dev";
        } elseif ($branch == "master") {
            $dbEnv = "production";
        }

        return $dbEnv;
    }

    private function syncProductPlugins($pluginName, $path, $branch, $dbEnv)
    {
        Log::info("Processing Git product plugin $path, $this->repoUrl, $dbEnv");
        $nameWithoutExtension = Str::lower(pathinfo($pluginName, PATHINFO_FILENAME));

        if (!$this->productModel) {
            Log::info("Product model is not available $path, $this->repoUrl, $dbEnv");
            return;
        }
        $dbData = [];
        $fileContent = $this->giteaApiService->getContent($this->repoUrl, $path, $branch);
        $data = Yaml::parse(base64_decode($fileContent["content"]));

        $this->productModel->load("productSoftwares");
        $productSoftwate = $this->productModel->productSoftwares->where("environment", $dbEnv)->first();

        $sectionIndex = null;
        if ($productSoftwate["supported_version_type"] == 1) {
            $sectionIndex = $productSoftwate["supported_version"];
        } elseif ($productSoftwate["supported_version_type"] == 2) {
            preg_match('/v(\d+)\.(\d+)\.(\d+)/', $productSoftwate["supported_version"], $matches);
            $majorVersion = $matches[1];
            $minorVersion = $matches[2];
            $sectionIndex = $majorVersion . $minorVersion;
        }

        if (!array_key_exists($sectionIndex, $data)) {
            Log::warning(
                "Selected moodle version is not available in plugin yml
                $pluginName, $sectionIndex, $this->repoUrl, $dbEnv"
            );
            return;
        } else {
            $lastSection = $data[$sectionIndex];
        }

        $lastSection = $lastSection[array_keys($lastSection)[0]];

        if (is_array($lastSection["tag_prefix"])) {
            $lastSection["tag_prefix"] = implode('', $lastSection["tag_prefix"]);
        }

        if (strpos($lastSection["tag_prefix"], "branch") !== false) {
            $dbData["selected_version_type"] = 1;
            $dbData["selected_version"] = $lastSection["version"];
        } elseif (in_array(trim($lastSection["tag_prefix"]), ['v', "V", ""])) {
            $dbData["selected_version"] = $lastSection["tag_prefix"] . $lastSection["version"];
            $dbData["selected_version_type"] = 2;
        }

        $location = $lastSection["location"];


        $filteredPlugins = $this->productModel->productPlugins->filter(function ($item) use ($location, $dbEnv) {
            return str_ends_with($item->plugin->git_url, $location) && $item->environment == $dbEnv;
        });
        $dbProductPlugin = $filteredPlugins->first();

        if ($dbProductPlugin) {
            $dbProductPlugin->update($dbData);
            Log::info("Updated DB product plugin $dbEnv", [$dbProductPlugin->plugin->git_url, $dbData]);
        } else {
            $dbPlugin = Plugin::where("git_url", "LIKE", "%" . $location)
                ->first();

            if ($dbPlugin) {
                $dbData['product_id'] = $this->productModel->id;
                $dbData['plugin_id'] = $dbPlugin->id;
                $dbData['environment'] = $dbEnv;
                $dbData['created_at'] = Carbon::now();
                $dbData['updated_at'] = Carbon::now();

                ProductHasPlugin::create($dbData);

                Log::info("Created DB product plugin for $this->repoUrl, $dbEnv", [$dbData]);
            } else {
                Log::warning("DB plugin: $nameWithoutExtension is not available for $this->repoUrl, $dbEnv");
            }
        }

    }

    private function createProductFromGit()
    {
        $productPackage = $this->extractProductFromGitRepo($this->repoObj["html_url"], $this->repoObj["name"]);
        if ($productPackage) {
            $productId = $this->productService->storeProductFromGit($productPackage);
            if ($productId) {
                $this->productModel = Product::find($productId);
                $this->syncPluginsInBranches();
            }
        }
    }
}
