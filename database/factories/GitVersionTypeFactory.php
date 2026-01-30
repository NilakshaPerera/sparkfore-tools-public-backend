<?php

namespace Database\Factories;

use App\Domain\Models\GitVersionType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Model>
 */
class GitVersionTypeFactory extends Factory
{
    protected $model = GitVersionType::class;
    public function definition(): array
    {
        return [
            'id' => 1,
            'name' => 'branch',
        ];
    }
}
