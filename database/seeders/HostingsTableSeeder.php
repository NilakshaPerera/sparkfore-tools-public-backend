<?php

namespace database\seeders;

use App\Domain\Models\BasePackage;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class HostingsTableSeeder extends Seeder
{
    protected $data = [  // Small All-in-One, Standard, Medium, Large, Extra Large
        [
            'name' => 'Small All-in-One',
            'production_price_month' => 1000,
            'staging_price_month' => 1000,
            'yearly_price_increase' => '3',
            'description' => 'This hosting package is suitable for very small organisations with 50 users or less. Technical specification: App vCPU: 2 App memory (GB): 2 App disk (GB): 60 DB vCPUs: - DB memory: - Secondary DB: -',
            'config' => '1_Small_AiO',
            'hosting_type_id' => 1,
            'hosting_provider_id' => 1,
            'hosting_location' => 'Sto2',
            'created_at' => '9/12/2023',
            'updated_at' => '9/12/2023',
            'disk_size' => 60,
            'base_package_name' => 'Small All-in-One',
        ],
        [
            'name' => 'Standard',
            'production_price_month' => 1500,
            'staging_price_month' => 1200,
            'yearly_price_increase' => '3',
            'description' => 'This hosting package is suitable for small organisations with 100 users or less. Technical specification: App vCPU: 2 App memory (GB): 2 App disk (GB): 60 DB vCPUs: 2 DB memory: 2 Secondary DB: -',
            'config' => '1_Standard',
            'hosting_type_id' => 1,
            'hosting_provider_id' => 1,
            'hosting_location' => 'Sto2',
            'created_at' => '9/12/2023',
            'updated_at' => '9/12/2023',
            'disk_size' => 60,
            'base_package_name' => 'Standard',
        ],
        [
            'name' => 'Standard+',
            'production_price_month' => 2300,
            'staging_price_month' => 1440,
            'yearly_price_increase' => '3',
            'description' => 'This hosting package is suitable for small organisations with 100 users or less, but with higher activity on the platform. Technical specification: App vCPU: 2 App memory (GB): 2 App disk (GB): 60 DB vCPUs: 2 DB memory: 2 Secondary DB: Yes',
            'config' => '1_Standard+',
            'hosting_type_id' => 1,
            'hosting_provider_id' => 1,
            'hosting_location' => 'Sto2',
            'created_at' => '9/12/2023',
            'updated_at' => '9/12/2023',
            'disk_size' => 60,
            'base_package_name' => 'Standard+',
            'delete' => true
        ],
        [
            'name' => 'Medium',
            'production_price_month' => 3500,
            'staging_price_month' => 1800,
            'yearly_price_increase' => '3',
            'description' => 'This hosting package is suitable for medium organisations with 200 users or less. Technical specification: App vCPU: 2 App memory (GB): 4 App disk (GB): 80 DB vCPUs: 2 DB memory: 4 Secondary DB: -',
            'config' => '1_Medium',
            'hosting_type_id' => 1,
            'hosting_provider_id' => 1,
            'hosting_location' => 'Sto2',
            'created_at' => '9/12/2023',
            'updated_at' => '9/12/2023',
            'disk_size' => 80,
            'base_package_name' => 'Medium',
        ],
        [
            'name' => 'Medium+',
            'production_price_month' => 5300,
            'staging_price_month' => 2200,
            'yearly_price_increase' => '3',
            'description' => 'This hosting package is suitable for medium organisations with 200 users or less, but with high activity in the system. Technical specification: App vCPU: 2 App memory (GB): 4 App disk (GB): 80 DB vCPUs: 2 DB memory: 4 Secondary DB: Yes',
            'config' => '1_Medium+',
            'hosting_type_id' => 1,
            'hosting_provider_id' => 1,
            'hosting_location' => 'Sto2',
            'created_at' => '9/12/2023',
            'updated_at' => '9/12/2023',
            'disk_size' => 80,
            'base_package_name' => 'Medium+',
            'delete' => true
        ],
        [
            'name' => 'Large',
            'production_price_month' => 8000,
            'staging_price_month' => 2700,
            'yearly_price_increase' => '3',
            'description' => 'This hosting package is suitable for large organisations with 3000 users or less. Technical specification: App vCPU: 4 App memory (GB): 8 App disk (GB): 160 DB vCPUs: 4 DB memory: 8 Secondary DB: -',
            'config' => '1_Large',
            'hosting_type_id' => 1,
            'hosting_provider_id' => 1,
            'hosting_location' => 'Sto2',
            'created_at' => '9/12/2023',
            'updated_at' => '9/12/2023',
            'disk_size' => 160,
            'base_package_name' => 'Large',
        ],
        [
            'name' => 'Large+',
            'production_price_month' => 12000,
            'staging_price_month' => 3300,
            'yearly_price_increase' => '3',
            'description' => 'This hosting package is suitable for large organisations with 3000 users or less, but with high activity in the system. Technical specification: App vCPU: 4 App memory (GB): 8 App disk (GB): 160 DB vCPUs: 4 DB memory: 8 Secondary DB: Yes',
            'config' => '1_Large+',
            'hosting_type_id' => 1,
            'hosting_provider_id' => 1,
            'hosting_location' => 'Sto2',
            'created_at' => '9/12/2023',
            'updated_at' => '9/12/2023',
            'disk_size' => 160,
            'base_package_name' => 'Large+',
            'delete' => true
        ],
        [
            'name' => 'Extra Large',
            'production_price_month' => 20000,
            'staging_price_month' => 3800,
            'yearly_price_increase' => '3',
            'description' => 'This hosting package is suitable for large organisations with 8000 users or less. Technical specification: App vCPU: 4 App memory (GB): 16 App disk (GB): 320 DB vCPUs: 4 DB memory: 8 Secondary DB: -',
            'config' => '1_Extra_Large',
            'hosting_type_id' => 1,
            'hosting_provider_id' => 1,
            'hosting_location' => 'Sto2',
            'created_at' => '9/12/2023',
            'updated_at' => '9/12/2023',
            'disk_size' => 320,
            'base_package_name' => 'Extra Large',
        ],
        [
            'name' => 'Extra Large+',
            'production_price_month' => 30000,
            'staging_price_month' => 4300,
            'yearly_price_increase' => '3',
            'description' => 'This hosting package is suitable for large organisations with 8000 users or less, but with high activity in the system. Technical specification: App vCPU: 4 App memory (GB): 16 App disk (GB): 320 DB vCPUs: 4 DB memory: 8 Secondary DB: Yes',
            'config' => '1_Extra_Large+',
            'hosting_type_id' => 1,
            'hosting_provider_id' => 1,
            'hosting_location' => 'Sto2',
            'created_at' => '9/12/2023',
            'updated_at' => '9/12/2023',
            'disk_size' => 320,
            'base_package_name' => 'Extra Large+',
            'delete' => true
        ],
        [
            'name' => 'Small All-in-One',
            'production_price_month' => null,
            'staging_price_month' => null,
            'yearly_price_increase' => null,
            'description' => 'This hosting package is suitable for very small organisations with 50 users or less. Technical specification: App vCPU: 2 App memory (GB): 2 App disk (GB): 60 DB vCPUs: - DB memory: - Secondary DB: -',
            'config' => '2_Small_AiO',
            'hosting_type_id' => 1,
            'hosting_provider_id' => 2,
            'hosting_location' => 'fra1',
            'created_at' => '9/12/2023',
            'updated_at' => '9/12/2023',
            'disk_size' => 60,
            'base_package_name' => 'Small All-in-One',
        ],
        [
            'name' => 'Standard',
            'production_price_month' => null,
            'staging_price_month' => null,
            'yearly_price_increase' => null,
            'description' => 'This hosting package is suitable for small organisations with 100 users or less. Technical specification: App vCPU: 2 App memory (GB): 2 App disk (GB): 60 DB vCPUs: 1 DB memory: 2 Secondary DB: -',
            'config' => '2_Standard_Standard',
            'hosting_type_id' => 1,
            'hosting_provider_id' => 2,
            'hosting_location' => 'fra1',
            'created_at' => '9/12/2023',
            'updated_at' => '9/12/2023',
            'disk_size' => 60,
            'base_package_name' => 'Standard',
        ],
        [
            'name' => 'Standard+',
            'production_price_month' => null,
            'staging_price_month' => null,
            'yearly_price_increase' => null,
            'description' => 'This hosting package is suitable for small organisations with 100 users or less, but with higher activity on the platform. Technical specification: App vCPU: 2 App memory (GB): 2 App disk (GB): 60 DB vCPUs: 1 DB memory: 2 Secondary DB: Yes',
            'config' => '2_Standard_Standard+',
            'hosting_type_id' => 1,
            'hosting_provider_id' => 2,
            'hosting_location' => 'fra1',
            'created_at' => '9/12/2023',
            'updated_at' => '9/12/2023',
            'disk_size' => 60,
            'base_package_name' => 'Standard+',
            'delete' => true
        ],
        [
            'name' => 'Medium',
            'production_price_month' => null,
            'staging_price_month' => null,
            'yearly_price_increase' => null,
            'description' => 'This hosting package is suitable for medium organisations with 200 users or less. Technical specification: App vCPU: 2 App memory (GB): 4 App disk (GB): 80 DB vCPUs: 2 DB memory: 4 Secondary DB: -',
            'config' => '2_Medium_Medium',
            'hosting_type_id' => 1,
            'hosting_provider_id' => 2,
            'hosting_location' => 'fra1',
            'created_at' => '9/12/2023',
            'updated_at' => '9/12/2023',
            'disk_size' => 80,
            'base_package_name' => 'Medium',
        ],
        [
            'name' => 'Medium+',
            'production_price_month' => null,
            'staging_price_month' => null,
            'yearly_price_increase' => null,
            'description' => 'This hosting package is suitable for medium organisations with 200 users or less, but with high activity in the system. Technical specification: App vCPU: 2 App memory (GB): 4 App disk (GB): 80 DB vCPUs: 2 DB memory: 4 Secondary DB: Yes',
            'config' => '2_Medium_Medium+',
            'hosting_type_id' => 1,
            'hosting_provider_id' => 2,
            'hosting_location' => 'fra1',
            'created_at' => '9/12/2023',
            'updated_at' => '9/12/2023',
            'disk_size' => 80,
            'base_package_name' => 'Medium+',
            'delete' => true
        ],
        [
            'name' => 'Large',
            'production_price_month' => null,
            'staging_price_month' => null,
            'yearly_price_increase' => null,
            'description' => 'This hosting package is suitable for large organisations with 3000 users or less. Technical specification: App vCPU: 4 App memory (GB): 8 App disk (GB): 160 DB vCPUs: 4 DB memory: 8 Secondary DB: -',
            'config' => '2_Large_Large',
            'hosting_type_id' => 1,
            'hosting_provider_id' => 2,
            'hosting_location' => 'fra1',
            'created_at' => '9/12/2023',
            'updated_at' => '9/12/2023',
            'disk_size' => 160,
            'base_package_name' => 'Large',
        ],
        [
            'name' => 'Large+',
            'production_price_month' => null,
            'staging_price_month' => null,
            'yearly_price_increase' => null,
            'description' => 'This hosting package is suitable for large organisations with 3000 users or less, but with high activity in the system. Technical specification: App vCPU: 4 App memory (GB): 8 App disk (GB): 160 DB vCPUs: 4 DB memory: 8 Secondary DB: Yes',
            'config' => '2_Large_Large+',
            'hosting_type_id' => 1,
            'hosting_provider_id' => 2,
            'hosting_location' => 'fra1',
            'created_at' => '9/12/2023',
            'updated_at' => '9/12/2023',
            'disk_size' => 160,
            'base_package_name' => 'Large+',
            'delete' => true
        ],
        [
            'name' => 'Extra Large',
            'production_price_month' => null,
            'staging_price_month' => null,
            'yearly_price_increase' => null,
            'description' => 'This hosting package is suitable for large organisations with 8000 users or less Technical specification:App vCPU: 4 App memory (GB): 16 App disk (GB): 320 DB vCPUs: 4 DB memory: 8 Secondary DB: -',
            'config' => '2_Extra_Large',
            'hosting_type_id' => 1,
            'hosting_provider_id' => 2,
            'hosting_location' => 'fra1',
            'created_at' => '9/12/2023',
            'updated_at' => '9/12/2023',
            'disk_size' => 320,
            'base_package_name' => 'Extra Large',
        ],
        [
            'name' => 'Extra Large+',
            'production_price_month' => null,
            'staging_price_month' => null,
            'yearly_price_increase' => null,
            'description' => 'This hosting package is suitable for large organisations with 8000 users or less, but with high activity in the system. Technical specification: App vCPU: 4 App memory (GB): 16 App disk (GB): 320 DB vCPUs: 4 DB memory: 8 Secondary DB: Yes',
            'config' => '2_Extra_Large+',
            'hosting_type_id' => 1,
            'hosting_provider_id' => 2,
            'hosting_location' => 'fra1',
            'created_at' => '9/12/2023',
            'updated_at' => '9/12/2023',
            'disk_size' => 320,
            'base_package_name' => 'Extra Large+',
            'delete' => true
        ],
        [
            'name' => 'Medium All-in-One [Deprecated]',
            'production_price_month' => null,
            'staging_price_month' => null,
            'yearly_price_increase' => null,
            'description' => 'This hosting package is DEPRECATED Technical specification: App vCPU: 2 App memory (GB): 4 App disk (GB): 80 DB vCPUs: - DB memory: - Secondary DB: -',
            'config' => '2_Medium_AiO_Deprecated',
            'hosting_type_id' => 1,
            'hosting_provider_id' => 2,
            'hosting_location' => 'fra1',
            'created_at' => '9/12/2023',
            'updated_at' => '9/12/2023',
            'disk_size' => 80,
            'base_package_name' => 'Medium All-in-One [Deprecated]',
        ],
        [
            'name' => 'Large All-in-One [Deprecated]',
            'production_price_month' => null,
            'staging_price_month' => null,
            'yearly_price_increase' => null,
            'description' => 'This hosting package is DEPRECATED Technical specification: App vCPU: 4 App memory (GB): 8 App disk (GB): 160 DB vCPUs: - DB memory: - Secondary DB: -',
            'config' => '2_Large_AiO_Deprecated',
            'hosting_type_id' => 1,
            'hosting_provider_id' => 2,
            'hosting_location' => 'fra1',
            'created_at' => '9/12/2023',
            'updated_at' => '9/12/2023',
            'disk_size' => 160,
            'base_package_name' => 'Large All-in-One [Deprecated]',
        ],
        [
            'name' => 'Extra Large All-in-One [Deprecated]',
            'production_price_month' => null,
            'staging_price_month' => null,
            'yearly_price_increase' => null,
            'description' => 'This hosting package is DEPRECATED Technical specification: App vCPU: 4 App memory (GB): 16 App disk (GB): 320 DB vCPUs: - DB memory: - Secondary DB: -',
            'config' => '2_Extra_Large_AiO_Deprecated',
            'hosting_type_id' => 1,
            'hosting_provider_id' => 2,
            'hosting_location' => 'fra1',
            'created_at' => '9/12/2023',
            'updated_at' => '9/12/2023',
            'disk_size' => 320,
            'base_package_name' => 'Extra Large All-in-One [Deprecated]',
        ],
        [
            'name' => 'On-Prem',
            'production_price_month' => null,
            'staging_price_month' => null,
            'yearly_price_increase' => null,
            'description' => null,
            'config' => '3_On_Prem',
            'hosting_type_id' => 2,
            'hosting_provider_id' => 3,
            'hosting_location' => 'fra1',
            'created_at' => '9/12/2023',
            'updated_at' => '9/12/2023',
            'disk_size' => null,
            'base_package_name' => 'On-Prem',
        ],
        [
            'name' => 'Hoganas',
            'production_price_month' => null,
            'staging_price_month' => null,
            'yearly_price_increase' => null,
            'description' => null,
            'config' => '2_Hoganas_Hoganas',
            'hosting_type_id' => 1,
            'hosting_provider_id' => 2,
            'hosting_location' => 'fra1',
            'created_at' => '9/12/2023',
            'updated_at' => '9/12/2023',
            'disk_size' => null,
            'base_package_name' => 'Hoganas',
        ],
        [
            'name' => 'Small All-in-One',
            'production_price_month' => null,
            'staging_price_month' => null,
            'yearly_price_increase' => null,
            'description' => 'This hosting package is suitable for very small organisations with 50 users or less. Technical specification: App vCPU: 2 App memory (GB): 2 App disk (GB): 60 DB vCPUs: - DB memory: - Secondary DB: -',
            'config' => '4_Small_AiO',
            'hosting_type_id' => 1,
            'hosting_provider_id' => 4,
            'hosting_location' => 'fra1',
            'created_at' => '9/12/2023',
            'updated_at' => '9/12/2023',
            'disk_size' => 60,
            'base_package_name' => 'Small All-in-One',
        ],
        [
            'name' => 'Medium All-in-One [Deprecated]',
            'production_price_month' => null,
            'staging_price_month' => null,
            'yearly_price_increase' => null,
            'description' => 'This hosting package is DEPRECATED Technical specification: App vCPU: 2 App memory (GB): 4 App disk (GB): 80 DB vCPUs: - DB memory: - Secondary DB: -',
            'config' => '4_Medium_AiO_Deprecated',
            'hosting_type_id' => 1,
            'hosting_provider_id' => 4,
            'hosting_location' => 'fra1',
            'created_at' => '9/12/2023',
            'updated_at' => '9/12/2023',
            'disk_size' => 80,
            'base_package_name' => 'Medium All-in-One [Deprecated]',
        ],
    ];
    public function run()
    {
        DB::table('hosting_providers')->updateOrInsert(['key' => 'cleura'], ['name' => 'Cleura', 'config' => '{}']);
        DB::table('hosting_providers')->updateOrInsert(['key' => 'digitalocean'], ['name' => 'Digital Ocean', 'config' => '{}']);
        DB::table('hosting_providers')->updateOrInsert(['key' => 'on-prem'], ['name' => 'On-Prem', 'config' => '{}']);
        DB::table('hosting_providers')->updateOrInsert(['key' => 'digitalocean2'], ['name' => 'Digital Ocean legacy', 'config' => '{}', 'active' => 0]);


        foreach ($this->data as $hosting) {
            $type = $hosting['hosting_type_id'];

            $hostingId = DB::table('hostings')
                ->where('name', $hosting['name'])
                ->where('hosting_provider_id', $hosting['hosting_provider_id'])
                ->value('id');

            if (isset($hosting['delete']) && $hosting['delete']) {
                $hosting['deleted_at'] = Carbon::now()->toDateTimeString();
            }

            unset($hosting['delete']);

            DB::table('hostings')
                ->where('name', $hosting['name'])
                ->where('hosting_provider_id', $hosting['hosting_provider_id'])
                ->update(array_merge(Arr::except($hosting, [
                    'base_package_name',
                    'production_price_month',
                    'staging_price_month',
                    'yearly_price_increase',
                    'disk_size'
                ]), [
                    "base_package_id" => $this->getBasePackageId($hosting['base_package_name']),
                    'updated_at' => Carbon::now()->toDateTimeString()
                ]));

            if (
                DB::table('hostings')
                    ->where('name', $hosting['name'])
                    ->where('hosting_provider_id', $hosting['hosting_provider_id'])
                    ->doesntExist()
            ) {
                DB::table('hostings')->insert(array_merge(
                    Arr::except($hosting, [
                        'base_package_name',
                        'production_price_month',
                        'staging_price_month',
                        'yearly_price_increase',
                        'disk_size'
                    ]),
                    [
                        "base_package_id" => $this->getBasePackageId($hosting['base_package_name']),
                        'created_at' => Carbon::now()->toDateTimeString(),
                        'updated_at' => Carbon::now()->toDateTimeString()
                    ]
                ));
            }

            $hostingId = DB::table('hostings')
                ->where('name', $hosting['name'])
                ->where('config', $hosting['config'])
                ->value('id');

            $basePackage = DB::table('base_packages')
                ->where('name', 'like', "%" . $hosting['name'] . "%")
                ->first();

            try {
                // Cloud providers
                if ($type == 1) {
                    DB::table('hosting_cloud_settings')->updateOrInsert(
                        ['hosting_id' => $hostingId],
                        [
                            'hosting_provider_id' => $hosting['hosting_provider_id'],
                            'base_package_id' => $basePackage->id ?? 1,
                            'backup_price_monthly' => 0,
                            'staging_price_monthly' => 0,
                            'active' => true,
                            'created_at' => Carbon::now()->toDateTimeString(),
                            'updated_at' => Carbon::now()->toDateTimeString()
                        ]
                    );
                } else {
                    DB::table('hosting_on_prem_settings')->updateOrInsert(
                        ['hosting_id' => $hostingId],
                        [
                            'active' => true,
                            'moodle_url' => 'http://sample',
                            'moodle_cron_url' => 'http://sample',
                            'reverse_proxy' => false,
                            'created_at' => Carbon::now()->toDateTimeString(),
                            'updated_at' => Carbon::now()->toDateTimeString()
                        ]
                    );
                }
            } catch (\Throwable $exception) {
                dd($exception->getMessage());
            }
        }
    }

    private function getBasePackageId($name)
    {
        $basePackage = BasePackage::where('name', $name)->first();

        if (isset($basePackage)) {
            return $basePackage->id;
        } else {
            return null;

        }
    }
}
