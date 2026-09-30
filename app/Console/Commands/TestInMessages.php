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
            $msisdn = '256781456492';
            $code = 'KPMZFT07';

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
                $response = Http::withoutVerifying()->post($url, [
                    'msisdn' => $item['msisdn'],
                    'message' => $item['code'],
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
