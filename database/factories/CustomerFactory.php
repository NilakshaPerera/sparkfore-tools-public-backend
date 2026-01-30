<?php

namespace Database\Factories;

use App\Domain\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Model>
 */
class CustomerFactory extends Factory
{

    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            //
        ];
    }
}
