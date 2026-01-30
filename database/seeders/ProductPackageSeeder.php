<?php

namespace Database\Seeders;

use App\Domain\Jobs\ProductSync;
use App\Domain\Models\Product;
use Illuminate\Database\Seeder;
use App\Domain\Services\ServiceApi\GiteaApiServiceInterface;
use App\Domain\Services\Product\ProductServiceInterface;
use App\Domain\Traits\GitProductPackage;
use Illuminate\Support\Facades\Cache;
use Log;


class ProductPackageSeeder extends Seeder
{
    use GitProductPackage;

    public function run(): void
    {
        $giteaService = app(GiteaApiServiceInterface::class);
        $productService = app(ProductServiceInterface::class);
        $allRepos = [];
        $pageNumber = 1;

        return;

        //Get all repos
        do {
            $repos = $giteaService->reposAvailable('LMS-Customer', $pageNumber);

            if (!empty($repos)) {
                $allRepos = array_merge($allRepos, $repos);
                $pageNumber++;
            }

        } while (!empty($repos));


        foreach ($allRepos as $repo) {
            $dbProduct = Product::where('git_url', $repo["html_url"])->first();
            if (!$dbProduct) {
                $productPackage = $this->extractProductFromGitRepo($repo["html_url"], $repo["name"]);
                if($productPackage) {
                    Log::info("Storing product from git", [$repo["html_url"]]);
                    $productService->storeProductFromGit($productPackage);
                }
            }

            $jobId = "ProductSync_" . hash('sha256', $repo["html_url"]);
            if (Cache::has($jobId) && in_array(Cache::get($jobId), ['started', 'queued'])) {
                Log::info("Product sync is already in progress", [$repo["html_url"]]);
            } else {
                dispatch(new ProductSync($repo["html_url"]))->onQueue('pluginsSync');
                Cache::put($jobId, 'queued', 3600); // Cache for 1 hour
            }
        }

    }

    public function getBaseUrl($url)
    {
        $parsedUrl = parse_url($url);
        $baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . '/';

        return $baseUrl;
    }
}
