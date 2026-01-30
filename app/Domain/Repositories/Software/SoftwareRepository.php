<?php

namespace App\Domain\Repositories\Software;

use App\Domain\Models\GitVersionType;
use App\Domain\Models\Software;
use App\Domain\Models\SoftwareVersion;
use App\Domain\Models\SoftwareSlug;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class SoftwareRepository implements SoftwareRepositoryInterface
{
    public function __construct(
        private Software $software,
        private GitVersionType $gitVersionType,
        private SoftwareVersion $softwareVersion,
        private SoftwareSlug $softwareSlug
        )
        {}

    /**
     * @param $params
     * @return LengthAwarePaginator|Builder[]|Collection
     */
    public function listSoftware($params)
    {
        // Modal
        $software = $this->software;

        if (!empty($with = ($params['with'] ?? false))) {
            $software = $software->with($with);
        }

        if (!empty($filter = ($params['filter'] ?? false))) {
            $software = $software->where(function ($q) use ($filter) {
                $q->where('name', 'ilike', "%$filter%");
                /*->orWhere('invoice_email', 'ilike', "%$filter%")
                    ->orWhere('invoice_address', 'ilike', "%$filter%");*/
            });
        }

        if (($params['sort_by'] ?? false) && ($params['sort_desc'] ?? false)) {
            $software =  $software->orderBy($params['sort_by'], $params['sort_desc']);
        } else {
            $software = $software->orderBy('id', 'desc');
        }

        if (!empty($page = ($params['page'] ?? false)) && !empty($perPage = ($params['per_page'] ?? false))) {
            return $software->paginate($perPage, ['*'], 'page', $page);
        }

        return $software->get();
    }

    /**
     * @return Collection|\Illuminate\Support\Collection
     */
    public function getFormCreate()
    {
        return $this->gitVersionType->all()->transform(
            fn($gitVersionType) =>
            [
                'text' => ucfirst($gitVersionType['name']),
                'value' => $gitVersionType['id']
            ]
        );
    }

    public function getAllSoftwareSlugs()
    {
          return $this->softwareSlug->all()->transform(
            fn($softwareSlug) =>
            [
                'id' => $softwareSlug['id'],
                'text' => ucfirst($softwareSlug['name']),
                'value' => $softwareSlug['value']
            ]
        );
    }

    public function getAllExistingSlugs()
    {
        return $this->software->pluck('slug')->filter()->values();
    }

    /**
     * @param $params
     * @return mixed
     */
    public function storeSoftware($params)
    {
        return $this->software::insertGetId($params);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function edit($id)
    {
        return $this->software::where('id', $id)->get()->transform(
            fn($software) =>
            [
                'id' => $software['id'],
                'name' => $software['name'],
                'name_slug' => $software['slug'],
                'software_slug_value' => ($software->softwareSlug) ? $software->softwareSlug->value : null,
                'git_url' => $software['git_url'],
                'git_version_type_id' => $software['git_version_type_id'],
                'version_supported' => $software['version_supported']
            ]
        )->first();
    }

    /**
     * @param $params
     * @return mixed
     */
    public function updateSoftware($params)
    {
        return $this->software::where('id', $params['id'])->update($params);
    }

    /**
     * @return mixed
     */
    public function getSoftware($id)
    {
        return $this->software->select(['id', 'name', 'git_url', 'git_version_type_id'])
            ->where('id', $id)
            ->first();
    }

    /**
     * @return mixed
     */
    public function getSoftwareByName($name)
    {
        return $this->software->select(['id', 'name', 'git_url', 'git_version_type_id'])
            ->whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->first();
    }

    /**
     * @param $softwareId, $versionType
     * @return mixed
     */
    public function getSoftwareVersions($softwareId, $versionType)
    {
        return $this->softwareVersion->where('software_id', $softwareId)
            ->where('version_type', $versionType)
            ->orderByDesc('version_id')
            ->get();
    }

    public function getLatestSoftwareVersions($softwareId, $versionType, $currentVersionId)
    {

        return $this->softwareVersion->where('software_id', $softwareId)
            ->whereRaw(
                'NULLIF(SUBSTRING(version_id FROM 1 FOR 10), \'\')::double precision >= ?',
                intval($currentVersionId)
                )
            ->where('version_type', $versionType)
            ->orderByDesc('version_id')
            ->get();
    }

    /**
     * @return mixed
     */
    public function getSoftwareById($id)
    {
        return $this->software->where('id', $id)->first();
    }
}
