<?php

namespace Database\Factories;

use App\Domain\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Model>
 */
class PermissionFactory extends Factory
{
    protected $model = Permission::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "module" => 1,
            "name" => "UNITTEST",
            "action" => "read",
            "description" => "UNITTEST_DESCRIPTION"
        ];
    }
}
