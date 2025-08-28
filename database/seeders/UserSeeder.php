<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;


class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {  
       
        DB::table('users')->insert([
            'name' => 'Root Name',
            'email' => env('ROOT_EMAIL'),
            'password' => bcrypt('secret'),
            'role' => 'admin',
            'created_at' => date(now()),
            'updated_at' => date(now())
        ]);

        DB::table('users')->insert([
            'name' => 'Maurice Kamugisha',
            'email' => 'maurice@nauticaltech.ug',
            'password' => bcrypt('secret'),
            'role' => 'user',
            'created_at' => date(now()),
            'updated_at' => date(now())
        ]);

        DB::table('users')->insert([
            'name' => 'Daniel Kayongo',
            'email' => 'daniel.kayongo@kansaiplascon.co.ug',
            'password' => bcrypt('w037GjfwzZ'),
            'role' => 'user',
            'created_at' => date(now()),
            'updated_at' => date(now())
        ]);
    }
}
