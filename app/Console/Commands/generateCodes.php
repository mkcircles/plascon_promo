<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Codes;
use Carbon\Carbon;

class GenerateCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:codes {count : The number of codes to generate} {brand=Pepsi : The brand for the codes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a specified number of promo codes for a given brand';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $count = (int) $this->argument('count');
        $brand = $this->argument('brand');

        if ($count <= 0) {
            $this->error('The count must be a positive integer.');
            return 1;
        }

        $prefixData = $this->getPrefix($brand);
        echo "Prefix Data: " . json_encode($prefixData) . PHP_EOL;
        $prefix = $prefixData['code'];
        $actualBrand = $prefixData['brand'];

        $this->info("Generating {$count} codes for brand '{$actualBrand}' with prefix '{$prefix}'...");

        $generated = 0;
        $attempts = 0;
        $maxAttempts = $count * 5; // Prevent infinite loops

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        while ($generated < $count && $attempts < $maxAttempts) {
            $attempts++;

            // Match CodesController logic
            if (strlen($prefix) == 3) {
                $suffix = $this->createCode(5);
            } elseif (strlen($prefix) == 4) {
                $suffix = $this->createCode(4);
            } else {
                $suffix = $this->createCode(8 - strlen($prefix));
            }

            $finalCode = $prefix . $suffix;

            if ($this->checkExistance($finalCode)) {
                $this->saveCode($finalCode, $actualBrand);
                $generated++;
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine();

        if ($generated < $count) {
            $this->warn("Successfully generated {$generated} of {$count} codes. Stop due to duplicate codes generation limit.");
        } else {
            $this->info("Successfully generated all {$generated} codes.");
        }

        return 0;
    }

    private function getPrefix($brand)
    {
        switch (strtolower($brand)) {
            case 'mirinda fruity':
                return ['code' => 'CBMF', 'brand' => 'Mirinda Fruity'];
            case 'mirinda green apple':
                return ['code' => 'CBMA', 'brand' => 'Mirinda Green Apple'];
            case 'mirinda orange':
                return ['code' => 'CBMZ', 'brand' => 'Mirinda Orange'];
            case 'mirinda pineapple':
                return ['code' => 'CBMP', 'brand' => 'Mirinda Pineapple'];
            default:
                return ['code' => 'CBP', 'brand' => 'Pepsi'];
        }
    }

    private function createCode($length)
    {
        $characters = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    private function checkExistance($finalCode)
    {
        return !Codes::where('code', $finalCode)->exists();
    }

    private function saveCode($finalCode, $brand)
    {
        Codes::insert([
            'code' => $finalCode,
            'brand' => $brand,
            'status' => 'pending',
            'inMessageId' => '',
            'prizeWon' => '',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
    }
}
