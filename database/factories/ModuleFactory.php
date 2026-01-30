<?php

namespace Database\Factories;

use App\Domain\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Model>
 */
class ModuleFactory extends Factory
{

    protected $model = Module::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "name" => "UNIT TEST MODULE",
        ];
    }
}
