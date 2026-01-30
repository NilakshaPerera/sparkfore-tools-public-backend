<?php

namespace App\Domain\Services\Plugin;

use App\Domain\Models\Plugin;

interface PluginServiceInterface
{
    public function getSoftwarePlugins($params, $customerId = null);

    public function getPluginVersions($id, $params);

    public function syncPlugin(Plugin $plugin);
    public function getPluginByURL($gitUrl);
}
