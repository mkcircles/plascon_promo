<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\PastWinner;

class BlacklistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $blockedNumbers = [

        ];

        foreach ($blockedNumbers as $black) {
            PastWinner::Create(['msisdn' => $black, 'category' => 'blacklisted', 'addedBy' => 1, 'created_at' => Carbon::now()]);
        }
    }
}
