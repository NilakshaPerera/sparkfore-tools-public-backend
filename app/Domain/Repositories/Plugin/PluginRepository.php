<?php

namespace App\Domain\Repositories\Plugin;

use App\Domain\Models\Customer;
use App\Domain\Models\GitVersionType;
use App\Domain\Models\Plugin;
use App\Domain\Models\PluginAvailableCustomer;
use App\Domain\Models\PluginSupportsSoftware;
use App\Domain\Models\PluginVersion;
use App\Domain\Models\Software;
use App\Domain\Models\SoftwareVersion;
use Illuminate\Database\Eloquent\Collection;
use Log;

class PluginRepository implements PluginRepositoryInterface
{
    public function __construct(
        private GitVersionType $gitVersionType,
        private Software $software,
        private Customer $customer,
        private Plugin $plugin,
        private PluginAvailableCustomer $pluginAvailableCustomer,
        private PluginSupportsSoftware $pluginSupportsSoftware,
        private PluginVersion $pluginVersion
    ) {
    }


    private function bindVersionCompabalityToPluginVersions($pluginVersionBuilder, $requiredVersionId)
    {
        $pluginVersionBuilder->select('*')
            ->where(function ($query) use ($requiredVersionId) {
                $query->where('required_version_id', '')
                    ->orWhereRaw(
                        'NULLIF(SUBSTRING(required_version_id FROM 1 FOR 10), \'\')::double precision <= ?',
                        [intval($requiredVersionId)]
                    );
            });

        return $requiredVersionId;
    }

    public function getRequiredVersionId($versionType, $softwareId, $supportedVersion): string
    {
        $requiredVersionId = SoftwareVersion::where('software_id', $softwareId)
            ->where('version_type', $versionType)
            ->where('version_name', $supportedVersion)->first()->version_id;

        return substr($requiredVersionId ?? '0', 0, 10);
    }


    /**
     * @return Builder[]|Collection
     */
    public function getSoftwarePlugins($params, $customerId = null)
    {
        $requiredVersionId = $this->getRequiredVersionId(
            $params['version_type'],
            $params['software_id'],
            $params['supported_version']
        );

        $plugin = $this->plugin->select(["id", "git_version_type_id", "name", "git_url"])

            ->with([
                'pluginVersions' => function ($query) use ($requiredVersionId) {
                    $this->bindVersionCompabalityToPluginVersions($query, $requiredVersionId);
                }
            ])
            ->whereHas("pluginSupportsSoftwares", function ($query) use ($params) {
                $query->where("software_id", $params['software_id']);
            });

        if ($customerId) {
            $plugin = $plugin->where(function ($query) use ($customerId) {
                $query->where('created_by', auth()->user()->id)
                    ->orWhere('availability', 'public')
                    ->orWhere(function ($query) use ($customerId) {
                        $query->whereHas('customers', function ($query) use ($customerId) {
                            $query->where('customer_id', $customerId);
                        });
                    });
            });
        }
        if (!empty($filter = ($params['filter'] ?? false))) {
            $plugin = $plugin->where(function ($q) use ($filter) {
                $q->where('name', 'ilike', "%$filter%")
                    ->orWhere('git_url', 'ilike', "%$filter%");
            });
        }

        if ($params['sort_by'] === 'name') {
            $plugin = $plugin->orderByRaw(
                "LOWER(CASE WHEN name ILIKE 'moodle-%' THEN SUBSTRING(name FROM 8) ELSE name END)
                {$params['sort_desc']}"
            );
        } else {
            $plugin = $plugin->orderBy($params['sort_by'], $params['sort_desc']);
        }


        return $plugin->get();
    }

    /**
     * @return Builder[]|Collection
     */
    public function getPluginsByUrl($url)
    {
        $plugin = $this->plugin->whereRaw('LOWER(github_url) = ?', [strtolower($url)]);
        return $plugin->get();
    }

    /**
     * @param $params
     * @return mixed
     */
    public function listPlugins($params, $customerId = null)
    {
        // Modal
        $plugin = $this->plugin;

        if (!empty($with = ($params['with'] ?? false))) {
            $plugin = $plugin->with($with);
        }

        if (!empty($filter = ($params['filter'] ?? false))) {
            $plugin = $plugin->where(function ($q) use ($filter) {
                $q->where('name', 'ilike', "%$filter%")
                    ->orWhere('git_url', 'ilike', "%$filter%")
                    ->orWhere(function ($q) use ($filter) {
                        $q->whereHas('softwares', function ($q) use ($filter) {
                            $q->where('name', 'ilike', "%$filter%");
                        });
                    })
                    ->orWhere(function ($q) use ($filter) {
                        $q->whereHas('customers', function ($q) use ($filter) {
                            $q->where('name', 'ilike', "%$filter%");
                        });
                    });
            });
        }

        if ($customerId) {
            $plugin = $plugin->where(function ($query) use ($customerId) {
                $query->where('created_by', auth()->user()->id)
                    ->orWhere('availability', 'public')
                    ->orWhere(function ($query) use ($customerId) {
                        $query->whereHas('customers', function ($query) use ($customerId) {
                            $query->where('customer_id', $customerId);
                        });
                    });
            });
        }

        if ($params['sort_by'] === 'name') {
            $plugin = $plugin->orderByRaw(
                "LOWER(CASE WHEN name ILIKE 'moodle-%' THEN SUBSTRING(name FROM 8) ELSE name END)
                {$params['sort_desc']}"
            );
        } else {
            $plugin = $plugin->orderBy($params['sort_by'], $params['sort_desc']);
        }

        if (!empty($page = ($params['page'] ?? false)) && !empty($perPage = ($params['per_page'] ?? false))) {
            return $plugin->paginate($perPage, ['*'], 'page', $page);
        }

        return $plugin->get();
    }

    /**
     * @return Collection|\Illuminate\Support\Collection
     */
    public function getGitVersionTypes()
    {
        return $this->gitVersionType->all()->transform(
            fn($gitVersionType) =>
            [
                'text' => ucfirst($gitVersionType['name']),
                'value' => $gitVersionType['id']
            ]
        );
    }

    /**
     * @return Collection|\Illuminate\Support\Collection
     */
    public function getSoftwares()
    {
        return $this->software->all()->transform(
            fn($software) =>
            [
                'label' => $software['name'],
                'value' => $software['id']
            ]
        );
    }

    /**
     * @return Collection|\Illuminate\Support\Collection
     */
    public function getCustomers()
    {
        return $this->customer->all()->transform(
            fn($customer) =>
            [
                'label' => $customer['name'],
                'value' => $customer['id']
            ]
        );
    }

    /**
     * @param $param
     * @return mixed
     */
    public function storePlugin($param)
    {
        return $this->plugin::insertGetId($param);
    }

    /**
     * @param $param
     * @return mixed
     */
    public function updatePlugin($param)
    {
        $this->plugin::where('id', $param['id'])->update($param);
        return $param['id'];
    }

    /**
     * @param $param
     * @return mixed
     */
    public function updatePluginByGitHubUrl($param, $githubUrl)
    {
        $this->plugin::where('github_url', $githubUrl)->update($param);
    }

    /**
     * @param $param
     * @return mixed
     */
    public function storePluginAvailableCustomers($param)
    {
        return $this->pluginAvailableCustomer::insertGetId($param);
    }

    /**
     * @param $pluginId
     * @return mixed
     */
    public function purgePluginAvailableCustomers($pluginId)
    {
        return $this->pluginAvailableCustomer->where('plugin_id', $pluginId)->delete();
    }

    /**
     * @param $param
     * @return mixed
     */
    public function storePluginSupportsSoftwares($param)
    {
        if (
            !$this->pluginSupportsSoftware::where('plugin_id', $param['plugin_id'])
                ->where('software_id', $param['software_id'])
                ->exists()
        ) {
            $this->pluginSupportsSoftware::insertGetId($param);
        }
    }

    /**
     * @param $pluginId
     * @return void
     */
    public function purgePluginSupportsSoftwares($pluginId)
    {
        $this->pluginSupportsSoftware->where('plugin_id', $pluginId)->delete();
    }

    public function edit($id)
    {
        return $this->plugin->with(['softwares', 'customers'])->find($id);
    }

    public function getPluginByURL($url)
    {
        Log::info("Getting pluggin by git URL ", [$url]);
        return $this->plugin->select(['id', 'description'])
            ->whereRaw('LOWER(git_url) = ?', [strtolower($url)])
            ->first();
    }

    /**
     * @param $pluginId, $versionType
     * @return mixed
     */
    public function getPluginVersions($pluginId, $versionType, $params = null)
    {
        $pluginVersions = $this->pluginVersion
            ->where('plugin_id', $pluginId)
            ->where('version_type', $versionType);

        if ($params && $params['softwareVersionType'] && $params['softwareId'] && $params['moodleVersion']) {
            $requiredVersionId = $this->getRequiredVersionId(
                $params['softwareVersionType'],
                $params['softwareId'],
                $params['moodleVersion']
            );
            $this->bindVersionCompabalityToPluginVersions(
                $pluginVersions,
                $requiredVersionId
            );
        }
        return $pluginVersions->orderByDesc('version_id')->get();
    }
}
