<?php

namespace Database\Factories;

use App\Domain\Models\Plugin;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Model>
 */
class PluginFactory extends Factory
{
    protected $model = Plugin::class;

    public function definition(): array
    {
        return [
            "git_version_type_id" => 1,
            "name" => "unittest plugin",
            "description" => "used in php unittests"
        ];
    }
}
