<?php

namespace Database\Factories;

use App\Domain\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Model>
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;
    public function definition(): array
    {
        return [
            'name' => 'admin'
        ];
    }
}
