<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use App\Models\User;


class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = [
            [
                'name' => 'Root Name',
                'email' => env('ROOT_EMAIL'),
                'password' => bcrypt('secret'),
                'role' => 'admin'
            ],
            [
                'name' => 'Maurice Kamugisha',
                'email' => 'maurice@nauticaltech.ug',
                'password' => bcrypt('secret'),
                'role' => 'user'
            ],
            [
                'name' => 'Daniel Kayongo',
                'email' => 'daniel.kayongo@kansaiplascon.co.ug',
                'password' => bcrypt('w037GjfwzZ'),
                'role' => 'user'
            ]
        ];


        foreach ($user as $key => $value) {
            User::create([
                "name" => $value["name"],
                "email" => $value["email"],
                "password" => $value["password"],
                "role" => $value["role"],
                "created_at" => date(now()),
                "updated_at" => date(now())
            ]);
        }



    }
}
