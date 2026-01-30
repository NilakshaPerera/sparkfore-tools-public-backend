<?php

namespace database\seeders;

use App\Domain\Models\BasePackage;
use Illuminate\Database\Seeder;

class HostingBasePackageSeeder extends Seeder
{
    public function run()
    {
        $basePackages = [
            ["name" => "Small All-in-One", "ansible_package_id" => 1],
            ["name" => "Standard", "ansible_package_id" => 2],
            ["name" => "Standard+", "ansible_package_id" => 3],
            ["name" => "Medium", "ansible_package_id" => 4],
            ["name" => "Medium+", "ansible_package_id" => 5],
            ["name" => "Large", "ansible_package_id" => 6],
            ["name" => "Large+", "ansible_package_id" => 7]
        ];

        foreach ($basePackages as $bp) {
           BasePackage::updateOrCreate(
            ['name' => $bp["name"]],
                [
                    'config' => '[]',
                    'ansible_package_id' => $bp["ansible_package_id"],
                    ]
            );
        }
    }

}
