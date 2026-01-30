<?php

namespace App\Domain\Traits;

use App\Domain\DataClasses\Product\GenerateProductChangeDTO;
use App\Domain\Models\Customer;
use App\Domain\Models\GitVersionType;
use App\Domain\Models\ProductChangeHistory;
use App\Domain\Models\Software;
use App\Domain\Repositories\Software\SoftwareRepositoryInterface;
use App\Domain\Services\ServiceApi\GiteaApiServiceInterface;
use Illuminate\Support\Facades\View;
use Log;
use Illuminate\Support\Str;
use Symfony\Component\Yaml\Yaml;

trait GitProductPackage
{

    private function extractProductDetailsFromGitName($originalGitName)
    {
        $availability = "private";
        $isLegacy = "false";
        $proName = "";
        $proSoftware = null;
        $proCustomer = null;
        $processedGitName = $originalGitName;

        if (str_starts_with($originalGitName, "shared-")) {
            $availability = "public";
            // replace start of the string with "shared-"
            $processedGitName = substr($processedGitName, strlen("shared-"));
        } else {
            $customers = Customer::select("id", "name", "slugified_name")->get();
            foreach ($customers as $customer) {
                if (
                    $processedGitName == $customer->slugified_name
                    || str_starts_with($processedGitName, $customer->slugified_name . "-")
                ) {
                    $proCustomer = $customer;
                    $processedGitName = substr($processedGitName, strlen($customer->slugified_name . "-"));
                    break;
                }
            }
        }

        $softwares = Software::select("name", "id")->get();

        foreach ($softwares as $software) {
            $softSlugName = Str::slug($software->name) . "-";
            if (str_starts_with($processedGitName, $softSlugName)) {
                $proSoftware = $software;
                $processedGitName = substr($processedGitName, strlen($softSlugName));
                break;
            }
        }

        if ($processedGitName == "") {
            $proName = $originalGitName;
            $isLegacy = "true";
        } else {
            $proName = $processedGitName;
        }


        $return = [
            "name" => $proName,
            "availability" => $availability,
            "software" => $proSoftware,
            "customer" => $proCustomer,
            "isLegacy" => $isLegacy
        ];
        Log::info("Git product details extracted from $originalGitName", $return);

        return $return;
    }

    private function getGitNameOfProduct($customerSlug, $softwareName, $productName): string
    {
        return $customerSlug . "-" . Str::slug($softwareName) . "-" . Str::slug($productName);
    }

    private function checkForCustomerYml($gitRepoUrl, $giteaService)
    {
        try {
            $customer = $giteaService->getContent($gitRepoUrl, 'core/customer.yml');
            return Yaml::parse(base64_decode($customer['content'] ?? ''));
        } catch (\Throwable $e) {
            Log::debug("$gitRepoUrl doesnot have customer.yml");
            $customer = $giteaService->getContent($gitRepoUrl, 'core/customer.yaml');
            return Yaml::parse(base64_decode($customer['content'] ?? ''));
        }
    }


    private function extractProductFromGitRepo($gitRepoUrl, $gitRepoName)
    {
        $softwareRepository = app(SoftwareRepositoryInterface::class);
        $giteaService = app(GiteaApiServiceInterface::class);
        $customerYml = null;
        try {
            $customerYml = $this->checkForCustomerYml($gitRepoUrl, $giteaService);
        } catch (\Throwable $th) {
            Log::debug("$gitRepoUrl doesnot have customer details");
        }

        $software = null;
        $softwareVersion = null;
        $softwareVersionType = 1;
        $gitProDetails = $this->extractProductDetailsFromGitName($gitRepoName);



        try {
            $moodle = $giteaService->getContent($gitRepoUrl, 'core/moodle.yaml');
            $software = $softwareRepository->getSoftwareByName('moodle');

            $parsed = Yaml::parse(base64_decode($moodle['content'] ?? ''));

            // Reverse the array to start from the bottom
            $reversedParsed = array_reverse($parsed, true);

            foreach ($reversedParsed as $key => $value) {
                if (isset($value['moodle'])) {
                    $softwareVersion = $value['moodle']['version'];
                    $value['moodle']['tag_prefix'] == '[branch]' ? $softwareVersionType = 1 : $softwareVersionType = 2;
                    break;
                }
            }
        } catch (\Throwable $e) {
            return;
        }

        $productPackage = [
            'software_id' => $software->id,
            'availability' => $gitProDetails["availability"],
            'supported_version_type' => $softwareVersionType,
            'product_name' => $gitProDetails["name"],
            'repo_url' => $gitRepoUrl,
            'legacy' => $gitProDetails["isLegacy"],
        ];

        if($customerYml && \Str::lower($customerYml["image_name"]) == "moodle") {
            $productPackage["legacy_product_name"] = "moodle";
        }

        if ($gitProDetails["availability"] == "private" && $gitProDetails["customer"]) {
            $productPackage["customer_id"] = $gitProDetails["customer"]["id"];
        }

        if (is_array($softwareVersion)) {
            $softwareVersion = implode('.', $softwareVersion);
        }

        if ($softwareVersionType == 2) {
            #handle tag name that has leading v and without v
            $productPackage['supported_version'] = "v"
                . str_replace("v", "", $softwareVersion);
        } else {
            $productPackage['supported_version'] = $softwareVersion;
        }

        return $productPackage;
    }

    /**
     * generate the product change commit message including changes of software and plugins.
     * Should call before updating the relevant product in DB
     * @return array
     */
    private function getProductChanges(GenerateProductChangeDTO $changeDto): array
    {
        $productChanges = [
            "softwareAdditions" => [],
            "softwareChanges" => []
        ];

        $gitVersionTypes = GitVersionType::whereIn("id", [1, 2])->get();

        if ($changeDto->getOldSoftware()) {
            if (
                $changeDto->getNewSoftware()["supported_version"] != $changeDto->getOldSoftware()["supported_version"]
                || $changeDto->getNewSoftware()["supported_version_type"]
                != $changeDto->getOldSoftware()["supported_version_type"]
            ) {
                $productChanges["softwareChanges"][] = [
                    "name" => $changeDto->getOldSoftware()["name"],
                    "from" => $changeDto->getOldSoftware()["supported_version"],
                    "from_version_type" => $gitVersionTypes
                        ->where("id", $changeDto->getOldSoftware()["supported_version_type"])->first()->name,
                    "to" => $changeDto->getNewSoftware()["supported_version"],
                    "to_version_type" => $gitVersionTypes
                        ->where("id", $changeDto->getNewSoftware()["supported_version_type"])->first()->name
                ];
            }
        } else {
            $productChanges["softwareAdditions"][] = [
                "name" => $changeDto->getNewSoftware()["name"],
                "version" => $changeDto->getNewSoftware()["supported_version"],
                "version_type" => $gitVersionTypes
                    ->where("id", $changeDto->getNewSoftware()["supported_version_type"])
                    ->first()->name,
            ];
        }


        $productChanges = array_merge($productChanges, $this->getAddedAndRemovedPlugins(
            $changeDto->getOldPlugins() ?? [],
            $changeDto->getNewPlugins() ?? [],
            $gitVersionTypes
        ));

        // Commit the changes
        $productChanges["commitMessage"] = View::make('templates.product-changes', [
            "productChanges" => $productChanges
        ])->render();

        if ($changeDto->getSaveChangeInDb() && $productChanges["commitMessage"] != "") {
            ProductChangeHistory::create([
                "product_id" => $changeDto->getNewDbProductObj()->id,
                "change" => $productChanges["commitMessage"],
                "branch" => $changeDto->getEnvironment(),
                "created_by" => auth()->user()->id
            ]);
        }
        return $productChanges;
    }


    private function getAddedAndRemovedPlugins(array $currentPlugins, array $newPlugins, $gitVersionTypes)
    {
        $pluginChanges = [];
        // Extract IDs from both arrays
        $currentPluginIds = array_column($currentPlugins, 'id');
        $newPluginIds = array_column($newPlugins, 'id');

        // Find added plugin IDs
        $addedPluginIds = array_diff($newPluginIds, $currentPluginIds);

        // Find removed plugin IDs
        $removedPluginIds = array_diff($currentPluginIds, $newPluginIds);

        foreach ($currentPlugins as $currentPlugin) {
            $plugin = collect($newPlugins)->firstWhere('id', $currentPlugin['id']);
            if ($plugin && (
                $currentPlugin["selected_version"] != $plugin["selected_version"] ||
                $currentPlugin["selected_version_type"] != $plugin["selected_version_type"]
            )) {
                $pluginChanges[] = [
                    "id" => $currentPlugin["id"],
                    "name" => $currentPlugin["name"],
                    "git_url" => $currentPlugin["git_url"],
                    "from" => $currentPlugin["selected_version"],
                    "from_version_type" => $gitVersionTypes
                        ->where("id", $currentPlugin["selected_version_type"])->first()->name,
                    "to" => $plugin["selected_version"],
                    "to_version_type" => $gitVersionTypes
                        ->where("id", $plugin["selected_version_type"])->first()->name
                ];
            }
        }



        // Find the actual plugin details for added plugins
        $addedPlugins = [];
        foreach ($newPlugins as $plugin) {
            if (empty($currentPlugins) || in_array($plugin['id'], $addedPluginIds)) {
                $plugin["version_type"] = $gitVersionTypes
                    ->where("id", $plugin["selected_version_type"])->first()->name;

                $addedPlugins[] = $plugin;
            }
        }

        // Find the actual plugin details for removed plugins
        $removedPlugins = [];
        foreach ($currentPlugins as $plugin) {
            if (in_array($plugin['id'], $removedPluginIds)) {
                $plugin["version_type"] = $gitVersionTypes
                    ->where("id", $plugin["selected_version_type"])->first()->name;

                $removedPlugins[] = $plugin;
            }
        }
        return [
            'addedPlugins' => $addedPlugins,
            'removedPlugins' => $removedPlugins,
            'pluginChanges' => $pluginChanges,
        ];
    }
}
