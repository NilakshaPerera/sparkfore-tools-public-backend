<?php

namespace App\Domain\Traits;

use App\Domain\Events\ProductBuildLogEvent;
use App\Domain\Exception\SparkforeException;
use App\Domain\Models\Product;
use App\Domain\Models\ProductBuild;
use App\Domain\Models\RemoteJob;
use App\Domain\Repositories\Product\ProductRepositoryInterface;
use App\Domain\Repositories\Remote\RemoteAdminRepositoryInterface;
use App\Domain\Services\Remote\RemoteCallHandlerInterface;
use Illuminate\Support\Str;
use Log;

trait PipelineTrait
{
    public function triggerBuildPipeline($params, $id, $productChanges = null)
    {
        $remoteAdminRepository = app(RemoteAdminRepositoryInterface::class);
        $remoteCallHandler = app(RemoteCallHandlerInterface::class);

        $jobId = $remoteAdminRepository->createRemoteJob(
            REMOTE_JOB_TYPE_BUILD_PIPELINE,
            $params['user_id'],
            $id,
            $params['environment']
        );

        $version = $params['software']['supported_version'];

        // for branches origin/ part needs in release note generation
        $gitVersion = "origin/" . $params['software']['supported_version'];

        if ($params['software']['supported_version_type'] == 2) {
            preg_match('/v(\d+)\.(\d+)\.(\d+)/', $params['software']['supported_version'], $matches);
            $majorVersion = $matches[1];
            $minorVersion = $matches[2];
            $version = $majorVersion . $minorVersion;
            $gitVersion = $params['software']['supported_version'];
        }

        $branch = 'develop';
        if ($params['environment'] == 'staging') {
            $branch = 'staging';
        } elseif ($params['environment'] == 'production') {
            $branch = 'master';
        }

        $this->setProductBuildData($jobId, $gitVersion, $productChanges);

        //call ancible API
        $responce = $remoteCallHandler->buildPipeline(
            $jobId,
            $params['customer'],
            $params['customer_slug'],
            $params['software']['name'],
            Str::slug($params['software']['name']),
            $params['product_name'],
            Str::slug($params['product_name']),
            $branch,
            $version,
            $params['legacy']
        );

        $job = [
            'response' => $responce
        ];

        if (isset($responce->object()->status)) {
            $job["callback_status"] = $responce->object()->status;
        }

        $remoteAdminRepository->updateRemoteJob(
            $jobId,
            $job
        );

        ProductBuildLogEvent::dispatch(RemoteJob::find($jobId));
    }

    private function setProductBuildData($remoteJobId, $gitVersion, $productChanges)
    {
        $changes = "";

        if ($productChanges != null) {
            $changes = [
                "softwareChanges" => $productChanges["softwareChanges"],
                "removedPlugins" => $productChanges["removedPlugins"],
                "addedPlugins" => $productChanges["addedPlugins"],
                "pluginChanges" => $productChanges["pluginChanges"],
            ];
        }

        ProductBuild::where('remote_job_id', $remoteJobId)
            ->update([
                'changes' => json_encode($changes),
                'git_version' => trim($gitVersion)
            ]);
    }

    private function getBuildParams(Product $product, $branch, $userId = 1)
    {
        $productRepository = app(ProductRepositoryInterface::class);
        if ($branch == "develop" || $branch == "Development") {
            $software = $productRepository->getSoftwareByProductAndEnvironment($product->id, "dev")->first();
        } else {
            $software = $productRepository->getSoftwareByProductAndEnvironment($product->id, $branch)->first();
        }

        $customerName = "shared";
        $customerSlug = "shared";
        $product->load("productCustomer.customer");

        if ($product->availability == "private") {
            if ($product->productCustomer && $product->productCustomer->customer) {
                $customerName = $product->productCustomer->customer->name;
                $customerSlug = $product->productCustomer->customer->slugified_name;
            } else {
                throw new SparkforeException("Private product has no associated customer.", 422);
            }
        }

        $return = [
            'environment' => $branch,
            'customer' => $customerName,
            'customer_slug' => $customerSlug,
            'user_id' => $userId,
            'product_name' => $product->pipeline_name,
            'legacy' => $product->legacy
        ];

        $return['software']["supported_version"] = $software->supported_version;
        $return['software']["supported_version_type"] = $software->supported_version_type;
        $return['software']["name"] = $software->software_name;

        return $return;
    }

    public function mergeChanges($oldChanges, $newChanges)
    {
        if (empty($oldChanges)) {
            Log::info("Old Changes is empty", [$oldChanges]);
            return $newChanges;
        }

        if (
            empty($newChanges['softwareAdditions'])
            && empty($newChanges['softwareChanges'])
            && empty($newChanges['addedPlugins'])
            && empty($newChanges['removedPlugins'])
            && empty($newChanges['pluginChanges'])
        ) {
            Log::info("New Changes are empty", [$newChanges]);
            return $oldChanges;
        }

        $this->mergeSoftwareChanges($oldChanges, $newChanges);

        // Merging addedPlugins
        $this->mergeAddedPlugins($oldChanges, $newChanges['addedPlugins']);

        // Merging removedPlugins
        $this->mergeRemovedPlugins($oldChanges, $newChanges['removedPlugins']);

        // Merging pluginChanges
        $this->mergePluginChanges($oldChanges, $newChanges['pluginChanges']);

        return $oldChanges;
    }

    // Helper method to merge addedPlugins
    private function mergeAddedPlugins(&$oldChanges, $newAddedPlugins)
    {
        foreach ($newAddedPlugins as $newAdded) {
            // Check for required keys in $newAdded
            if (!isset($newAdded['id'])) {
                continue; // Skip this plugin if essential keys are missing
            }

            // Check if the plugin is in removedPlugins
            $existingRemovedIndex = $this->findPluginById($oldChanges['removedPlugins'], $newAdded['id']);
            if ($existingRemovedIndex !== null) {
                Log::info("Plugin found in removedPlugins1", [$newAdded]);
                // Move from removed to added
                $oldChanges['removedPlugins'] = array_splice($oldChanges['removedPlugins'], $existingRemovedIndex, 1);
                $oldChanges['addedPlugins'][] = $newAdded;
            } else {
                // Check if plugin is already in addedPlugins with the same id and version
                $existingAddedIndex = $this->findPluginById($oldChanges['addedPlugins'], $newAdded['id']);
                if ($existingAddedIndex === null) {
                    Log::info("Plugin not found in addedPlugins1", [$newAdded]);
                    // Add new plugin to addedPlugins
                    $oldChanges['addedPlugins'][] = $newAdded;
                } else {
                    Log::info("Plugin found in addedPlugins1", [$newAdded]);
                    $oldChanges['addedPlugins'][$existingAddedIndex]['selected_version'] = $newAdded['selected_version'];
                    $oldChanges['addedPlugins'][$existingAddedIndex]['selected_version_type'] = $newAdded['selected_version_type'];
                    $oldChanges['addedPlugins'][$existingAddedIndex]['version_type'] = $newAdded['version_type'];
                }
            }
        }
    }

    // Helper method to merge removedPlugins
    private function mergeRemovedPlugins(&$oldChanges, $newRemovedPlugins)
    {
        foreach ($newRemovedPlugins as $newRemoved) {
            // Check for required keys in $newRemoved
            if (!isset($newRemoved['id'])) {
                continue; // Skip this plugin if essential keys are missing
            }

            // If plugin is in addedPlugins, move it to removedPlugins
            $existingAddedIndex = $this->findPluginById($oldChanges['addedPlugins'], $newRemoved['id']);
            if ($existingAddedIndex !== null) {
                // Move plugin to removedPlugins
                Log::info("Plugin found in addedPlugins2", [$newRemoved]);
                $oldChanges['removedPlugins'][] = $oldChanges['addedPlugins'][$existingAddedIndex];
                unset($oldChanges['addedPlugins'][$existingAddedIndex]);
            }

            // If plugin exists in pluginChanges, move it to removedPlugins
            $existingChangeIndex = $this->findPluginById($oldChanges['pluginChanges'], $newRemoved['id']);
            if ($existingChangeIndex !== null) {
                // Move it from pluginChanges to removedPlugins
                Log::info("Plugin found in pluginChanges2", [$newRemoved]);
                $oldChanges['removedPlugins'][] = $oldChanges['pluginChanges'][$existingChangeIndex];
                unset($oldChanges['pluginChanges'][$existingChangeIndex]);
            }
        }
    }

    // Helper method to merge pluginChanges
    private function mergePluginChanges(&$oldChanges, $newChanges)
    {
        // Ensure newChanges is not null and is an array, otherwise initialize it as an empty array
        $newChanges = $newChanges ?: [];

        foreach ($newChanges as $newChange) {
            // Check for required keys in $newChange
            if (!isset($newChange['id'])) {
                continue; // Skip this change if essential keys are missing
            }

            // Check if plugin exists in addedPlugins and update its version
            $existingAddedIndex = $this->findPluginById($oldChanges['addedPlugins'], $newChange['id']);

            if ($existingAddedIndex !== null) {
                Log::info("Plugin found in addedPlugins3", [$newChange]);
                // If the plugin is found in addedPlugins, update its version
                $oldChanges['addedPlugins'][$existingAddedIndex]['selected_version'] = $newChange['to'];
                $oldChanges['addedPlugins'][$existingAddedIndex]['selected_version_type'] = $newChange['to_version_type'];
                $oldChanges['addedPlugins'][$existingAddedIndex]['version_type'] = $newChange['to_version_type'];
            } else {
                Log::info("Plugin not found in addedPlugins3", [$newChange]);
                $existingChangedIndex = $this->findPluginById($oldChanges['pluginChanges'], $newChange['id']);
                if ($existingChangedIndex === null) {
                    // Add new change to pluginChanges
                    $oldChanges['pluginChanges'][] = $newChange;
                } else {
                    // Update existing change in pluginChanges
                    $oldChanges['pluginChanges'][$existingChangedIndex]['from'] = $newChange['from'];
                    $oldChanges['pluginChanges'][$existingChangedIndex]['from_version_type'] = $newChange['from_version_type'];
                    $oldChanges['pluginChanges'][$existingChangedIndex]['to'] = $newChange['to'];
                    $oldChanges['pluginChanges'][$existingChangedIndex]['to_version_type'] = $newChange['to_version_type'];
                }



                // If the plugin exists in removedPlugins, move it to pluginChanges
                $existingRemovedIndex = $this->findPluginById($oldChanges['removedPlugins'], $newChange['id']);

                if ($existingRemovedIndex !== null) {
                    Log::info("Plugin found in removedPlugins3", [$newChange]);
                    // Remove from removedPlugins and move it to pluginChanges
                    $oldChanges['removedPlugins'] = array_splice($oldChanges['removedPlugins'], $existingRemovedIndex, 1);
                }
            }



        }
    }

    // Helper method to find plugin by ID
    private function findPluginById($pluginArray, $id)
    {
        Log::info("Finding plugin by ID", [$pluginArray, $id]);
        foreach ($pluginArray as $index => $plugin) {
            if (intval($plugin['id']) === intval($id)) {
                return $index;
            }
        }
        return null;
    }

    private function mergeSoftwareChanges(&$oldChanges, $newChanges)
    {
        if(array_key_exists("softwareChanges", $newChanges) && empty($newChanges["softwareChanges"])){
                Log::info("New software changes are empty");
                return;
        }

        if(array_key_exists("softwareChanges", $oldChanges)){
            if(empty($oldChanges["softwareChanges"])){
                Log::info("Old software changes are empty");
                $oldChanges["softwareChanges"] = $newChanges["softwareChanges"];
            } else {
                Log::info("Old software changes are available");
                if(
                    $oldChanges["softwareChanges"][0]["from"] == $newChanges["softwareChanges"][0]["from"]
                    && $oldChanges["softwareChanges"][0]["from_version_type"] == $newChanges["softwareChanges"][0]["from_version_type"]
                    && $oldChanges["softwareChanges"][0]["to"] == $newChanges["softwareChanges"][0]["to"]
                    && $oldChanges["softwareChanges"][0]["to_version_type"] == $newChanges["softwareChanges"][0]["to_version_type"]
                ){
                    // selected previous version again
                    Log::info("Selected previous version again");
                    $oldChanges["softwareChanges"] = [];
                } else {
                    $oldChanges["softwareChanges"][0]["to"] = $newChanges["softwareChanges"][0]["to"];
                    $oldChanges["softwareChanges"][0]["to_version_type"] = $newChanges["softwareChanges"][0]["to_version_type"];
                }
            }

        } else {
            Log::info("Software changes not found in old changes");
            $oldChanges["softwareChanges"] = $newChanges["softwareChanges"];
        }
    }

}
