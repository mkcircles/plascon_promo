<?php
use App\Models\Codes;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

// Brands list
$brands = ['Vinyl Silk', 'Weatherguard', 'Anti-Mosquito', 'Super Gloss', 'Roof Paint'];

// Let's truncate tables first so we have clean test data
Codes::truncate();
DB::table('in_messages')->truncate();
DB::table('airtimes')->truncate();

echo "Truncated tables.\n";

// Generate and simulate
foreach ($brands as $brand) {
    echo "Simulating data for {$brand}...\n";
    
    // Prefix mapping
    $prefix = match($brand) {
        'Vinyl Silk' => 'PLVS',
        'Weatherguard' => 'PLWG',
        'Anti-Mosquito' => 'PLAM',
        'Super Gloss' => 'PLSG',
        'Roof Paint' => 'PLRP',
        default => 'PL',
    };

    // Let's generate 100 codes for this brand
    for ($i = 0; $i < 100; $i++) {
        $suffix = '';
        $characters = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        for ($j = 0; $j < 5; $j++) {
            $suffix .= $characters[rand(0, strlen($characters) - 1)];
        }
        $finalCode = $prefix . $suffix;

        // Randomize status
        // 70% chance of being used, 30% pending
        $isUsed = (rand(1, 10) <= 7);
        $status = $isUsed ? 'used' : 'pending';

        // Generate on July 1st
        $createdAt = Carbon::create(2026, 7, 1, rand(8, 18), rand(0, 59), rand(0, 59));
        
        // If used, set updated_at to a random date between July 1st and July 4th
        if ($isUsed) {
            $updatedAt = Carbon::create(2026, 7, rand(1, 4), rand(0, 23), rand(0, 59), rand(0, 59));
            $phone = '25677' . rand(100000, 999999);
            
            // Insert into in_messages
            $inMessageId = DB::table('in_messages')->insertGetId([
                'msisdn' => $phone,
                'inText' => $finalCode,
                'status' => 'valid',
                'response' => 'Congrats, you won 5000 Airtime.',
                'created_at' => $updatedAt,
                'updated_at' => $updatedAt,
            ]);

            // Insert into airtimes
            DB::table('airtimes')->insert([
                'inMessageId' => $inMessageId,
                'msisdn' => $phone,
                'amount' => '5000',
                'status' => 'success',
                'created_at' => $updatedAt,
                'updated_at' => $updatedAt,
            ]);
        } else {
            $updatedAt = $createdAt;
            $inMessageId = '';
        }

        Codes::create([
            'code' => $finalCode,
            'brand' => $brand,
            'status' => $status,
            'inMessageId' => $inMessageId,
            'prizeWon' => $isUsed ? 'Airtime - 5000' : '',
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ]);
    }
}

echo "Successfully simulated codes and records for all brands!\n";
