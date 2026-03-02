<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Codes;

class testCodeGeneration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:testCodeGeneration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test code generation setup and show current counts';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Testing Code Generation Setup');
        $this->info('============================');
        $this->info('');

        // Test database connection
        try {
            $totalCodes = Codes::count();
            $this->info("✓ Database connection successful");
            $this->info("Total codes in database: " . number_format($totalCodes));
        } catch (\Exception $e) {
            $this->error("✗ Database connection failed: " . $e->getMessage());
            return 1;
        }

        $this->info('');

        // Show current counts for each area
        $this->info('Current Code Counts by Area:');
        $this->info('-----------------------------');

        $areas = [
            'Jinja' => 'Jinja',
            'Mbale' => 'Mbale', 
            'Lira' => 'Lira',
            'Gulu' => 'Gulu',
            'Arua' => 'Arua',
            'Fort Portal' => 'Fort Portal',
            'Mbarara' => 'Mbarara',
            'Masaka' => 'Masaka',
            'Kampala' => 'Kampala'
        ];

        $totalNeeded = 0;
        foreach ($areas as $areaCode => $areaName) {
            $count = Codes::where('area', $areaName)->count();
            $needed = max(0, 30000 - $count);
            $totalNeeded += $needed;
            
            // Check cache status
            $cacheKey = "code_generation_{$areaCode}";
            $cacheStatus = cache()->get($cacheKey);
            $cacheIcon = $cacheStatus === 'completed' ? '🏆' : ($cacheStatus === 'processing' ? '⏳' : '🆕');
            
            $status = $count >= 30000 ? '✓' : '⚠️';
            $this->info("  {$status} {$areaName}: " . number_format($count) . " / 30,000 {$cacheIcon}");
            
            if ($needed > 0) {
                $this->info("      Need: " . number_format($needed) . " more codes");
            } else {
                $this->info("      Cache: " . ($cacheStatus ?: 'none'));
            }
        }

        $this->info('');
        $this->info("Total codes needed across all areas: " . number_format($totalNeeded));

        if ($totalNeeded > 0) {
            $this->info('');
            $this->info('To generate missing codes, run:');
            $this->info('  php artisan command:generateCodes');
        } else {
            $this->info('');
            $this->info('🎯 All areas have sufficient codes!');
        }

        return 0;
    }
}
