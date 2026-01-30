<?php

namespace App\Domain\Repositories\Plugin;

interface PluginRepositoryInterface
{
    public function listPlugins($params, $customerId=null);

    public function getSoftwarePlugins($params, $customerId=null);

    public function getPluginsByUrl($url);

    public function updatePluginByGitHubUrl($param, $githubUrl);

    public function getPluginByURL($url);

    public function getGitVersionTypes();

    public function getPluginVersions($pluginId, $versionType, $params=null);

    public function storePluginSupportsSoftwares($param);

    public function getRequiredVersionId($versionType, $softwareId, $supportedVersion);


}
