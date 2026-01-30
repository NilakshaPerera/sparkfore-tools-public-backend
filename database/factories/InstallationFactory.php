<?php

namespace Database\Factories;

use App\Domain\Models\Installation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Models\Installation>
 */
class InstallationFactory extends Factory
{
    protected $model = Installation::class;

    public function definition(): array
    {
        return [
            'id' => '58',
            'hosting_id' => '11',
            'url' => 'testing.sparkfore.com',
            'include_staging_package' => '0',
            'include_backup' => '1',
            'general_terms_agreement' => '1',
            'billing_terms_agreement' => '1',
            'date_contract_ends' => '2099-01-01',
            'date_contract_terminate' => '0',
            'installation_target_type_id' => 1,
            'customer_product_id' => 1,
            'domain_type' => 'standard'
        ];
    }
}
