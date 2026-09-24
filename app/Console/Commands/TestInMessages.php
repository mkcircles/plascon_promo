<?php

namespace App\Console\Commands;

use App\Models\Codes;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestInMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:in-messages {count=50 : The number of codes to test} {--url=https://plascon_promo.test/inmsg/receive : The endpoint URL}';

    /**
     * The console command aliases.
     *
     * @var array
     */
    protected $aliases = ['app:test-in-messages'];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the inmsg/receive endpoint with a mix of valid and invalid codes';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $count = (int) $this->argument('count');
        $url = $this->option('url');

        $this->info("Testing endpoint: {$url}");
        $this->info("Sending a mix of {$count} valid and invalid codes...");

        // Fetch available pending codes
        $validCodes = Codes::where('status', 'pending')
            ->limit($count)
            ->pluck('code')
            ->toArray();

        $testData = [];
        $usedPool = [];

        for ($i = 0; $i < $count; $i++) {
            $msisdn = '25677' . rand(1000000, 9999999);

            if ($i > 0 && $i % 12 === 0 && !empty($usedPool)) {
                // Already used code (re-send a previously used valid code)
                $code = $usedPool[array_rand($usedPool)];
            } elseif ($i % 5 === 0) {
                // Invalid non-existent code
                $code = 'INV' . strtoupper(substr(md5((string) rand()), 0, 5));
            } elseif ($i % 15 === 0) {
                // Unsupported network number (25671...)
                $msisdn = '25671' . rand(1000000, 9999999);
                $code = array_pop($validCodes) ?? 'KPMZTEST';
            } else {
                // Valid pending code
                $code = array_pop($validCodes) ?? 'KPMZTEST';
                $usedPool[] = $code;
            }

            $testData[] = [
                'msisdn' => $msisdn,
                'code' => $code,
            ];
        }

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $success = 0;

        foreach ($testData as $item) {
            try {
                $response = Http::withoutVerifying()->get($url, [
                    'msisdn' => $item['msisdn'],
                    'text' => $item['code'],
                ]);

                if ($response->successful()) {
                    $success++;
                }
            } catch (\Exception $e) {
                $this->error(" Request error: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Completed! {$success}/{$count} requests returned HTTP 200.");

        return 0;
    }
}
