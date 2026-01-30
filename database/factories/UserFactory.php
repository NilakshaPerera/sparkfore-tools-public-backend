<?php

namespace Database\Factories;

use App\Domain\Models\AccountType;
use App\Domain\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Domain\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $role = Role::factory()->create();
        $accountType = AccountType::factory()->create();

        return [
            'f_name' => 'unit',
            'l_name' => 'test',
            'email' => 'testoing@sparkfore.com',
            'password' => Hash::make('passrOQ5byMiYe4word'),
            'lang_id' => 1,
            'account_type_id' => $accountType->id,
            'trial' => true,
            'role_id' => $role->id
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
