<?php

namespace Database\Factories;

use App\Domain\Models\AccountType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Model>
 */
class AccountTypeFactory extends Factory
{

    protected $model = AccountType::class;

    public function definition(): array
    {
        return [
            'name' => 'customer'
        ];
    }
}
