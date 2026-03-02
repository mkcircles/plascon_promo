<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Codes;

class generateCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:generateCodes {--reset : Reset cache for all areas}';

    /**
     * The console description.
     *
     * @var string
     */
    protected $description = 'Generate 30,000 codes for all areas using URL calls';

    /**
     * The areas to generate codes for
     *
     * @var array
     */
    protected $areas = [
        'Jinja' => 'Jinja',
        'Mbale' => 'Mbale', 
        'Lira' => 'Lira',
        'Gulu' => 'Gulu',
        'Arua' => 'Arua',
        'Fort' => 'Fort Portal',
        'Mbarara' => 'Mbarara',
        'Masaka' => 'Masaka',
        'Kampala' => 'Kampala'
    ];

    /**
     * Target number of codes per area
     *
     * @var int
     */
    protected $targetCount = 30000;

    /**
     * Batch size for each generation call
     *
     * @var int
     */
    protected $batchSize = 100;

    /**
     * Delay between API calls (seconds)
     *
     * @var int
     */
    protected $delay = 15;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Handle reset option
        if ($this->option('reset')) {
            $this->resetAllAreaCache();
            return 0;
        }

        $this->info('Starting code generation for all areas...');
        $this->info('Target: ' . number_format($this->targetCount) . ' codes per area');
        $this->info('Batch size: ' . $this->batchSize . ' codes per call');
        $this->info('Delay between calls: ' . $this->delay . ' seconds');
        $this->info('');

        // Get areas that need codes (using cache to track progress)
        $pendingAreas = $this->getPendingAreas();
        
        if (empty($pendingAreas)) {
            $this->info('🎯 All areas already have sufficient codes!');
            return 0;
        }

        $this->info('Areas needing codes: ' . count($pendingAreas));
        $this->info('');

        foreach ($pendingAreas as $areaCode => $areaName) {
            $this->generateCodesForArea($areaCode, $areaName);
        }

        $this->info('');
        $this->info('Code generation completed for all pending areas!');
        
        return 0;
    }

    /**
     * Generate codes for a specific area
     *
     * @param string $areaCode
     * @param string $areaName
     * @return void
     */
    public function generateCodesForArea($areaCode, $areaName)
    {
        $this->info("Processing area: {$areaName} ({$areaCode})");
        
        // Set cache key for this area
        $cacheKey = "code_generation_{$areaCode}";
        cache()->put($cacheKey, 'processing', now()->addHours(2));
        
        // Get current count for this area
        $currentCount = $this->getAreaCodeCount($areaName);
        $this->info("Current codes: " . number_format($currentCount));
        
        if ($currentCount >= $this->targetCount) {
            $this->info("✓ {$areaName} already has sufficient codes ({$currentCount})");
            cache()->forget($cacheKey);
            return;
        }
        
        $needed = $this->targetCount - $currentCount;
        $this->info("Need to generate: " . number_format($needed) . " more codes");
        
        $batches = ceil($needed / $this->batchSize);
        $this->info("Will generate in {$batches} batches of {$this->batchSize}");
        
        $generated = 0;
        $batchNumber = 1;
        
        while ($currentCount < $this->targetCount && $batchNumber <= 100) { // Safety limit
            $this->info("  Batch {$batchNumber}: Generating {$this->batchSize} codes...");
            
            try {
                // Call the URL to generate codes
                $response = Http::get(url("/codes/generate/{$areaCode}/{$this->batchSize}"));
                
                if ($response->successful()) {
                    // Wait for the generation to complete
                    sleep($this->delay);
                    
                    // Check new count
                    $newCount = $this->getAreaCodeCount($areaName);
                    $batchGenerated = $newCount - $currentCount;
                    
                    $this->info("    ✓ Generated {$batchGenerated} codes (Total: {$newCount})");
                    
                    $currentCount = $newCount;
                    $generated += $batchGenerated;
                    
                    // If we're close to target, adjust batch size
                    if (($this->targetCount - $currentCount) < $this->batchSize) {
                        $this->batchSize = $this->targetCount - $currentCount;
                        $this->info("    Adjusted batch size to {$this->batchSize} for final batch");
                    }
                } else {
                    $this->error("    ✗ Failed to generate codes. Response: " . $response->status());
                    break;
                }
                
            } catch (\Exception $e) {
                $this->error("    ✗ Exception occurred: " . $e->getMessage());
                break;
            }
            
            $batchNumber++;
            
            // Progress update
            $progress = round(($currentCount / $this->targetCount) * 100, 1);
            $this->info("    Progress: {$progress}% ({$currentCount}/{$this->targetCount})");
        }
        
        $finalCount = $this->getAreaCodeCount($areaName);
        $this->info("✓ {$areaName}: Final count: " . number_format($finalCount));
        
        if ($finalCount >= $this->targetCount) {
            $this->info("  🎯 Target reached successfully!");
            // Mark area as completed in cache
            cache()->put($cacheKey, 'completed', now()->addDays(7));
        } else {
            $this->warn("  ⚠️  Target not fully reached. Generated: " . number_format($finalCount));
            cache()->forget($cacheKey);
        }
        
        $this->info('');
    }

    /**
     * Get areas that need codes (not completed)
     *
     * @return array
     */
    private function getPendingAreas()
    {
        $pendingAreas = [];
        
        foreach ($this->areas as $areaCode => $areaName) {
            $cacheKey = "code_generation_{$areaCode}";
            $cacheStatus = cache()->get($cacheKey);
            
            // Skip if area is marked as completed in cache
            if ($cacheStatus === 'completed') {
                $this->info("Skipping {$areaName} - marked as completed in cache");
                continue;
            }
            
            // Check current count
            $currentCount = $this->getAreaCodeCount($areaName);
            
            if ($currentCount < $this->targetCount) {
                $pendingAreas[$areaCode] = $areaName;
            } else {
                // Mark as completed since it already has enough codes
                cache()->put($cacheKey, 'completed', now()->addDays(7));
                $this->info("Marking {$areaName} as completed - already has {$currentCount} codes");
            }
        }
        
        return $pendingAreas;
    }

    /**
     * Reset cache for all areas (useful for testing or resetting the system)
     *
     * @return void
     */
    private function resetAllAreaCache()
    {
        $this->info('Resetting cache for all areas...');
        
        foreach ($this->areas as $areaCode => $areaName) {
            $cacheKey = "code_generation_{$areaCode}";
            cache()->forget($cacheKey);
            $this->info("✓ Cleared cache for {$areaName}");
        }
        
        $this->info('');
        $this->info('Cache reset completed! All areas will be processed again.');
    }

    /**
     * Get the current code count for an area
     *
     * @param string $areaName
     * @return int
     */
    private function getAreaCodeCount($areaName)
    {
        return Codes::where('area', $areaName)->count();
    }
}
