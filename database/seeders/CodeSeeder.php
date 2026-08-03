<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class CodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $brands = [
            ['name' => 'Mirinda Fruity', 'count' => 1000],
            ['name' => 'Mirinda Green Apple', 'count' => 600],
            ['name' => 'Mirinda Orange', 'count' => 200],
            ['name' => 'Mirinda Pineapple', 'count' => 200],
        ];

        foreach ($brands as $brand) {
            //We need to generate codes for each brand
            $name = $brand['name'];
            $count = $brand['count'];

            echo "Generating {$count} codes for brand '{$name}'...\n";

            Artisan::call("generate:codes {$count} \"{$name}\" ");
            //echo Artisan::output();
        }
    }
}
