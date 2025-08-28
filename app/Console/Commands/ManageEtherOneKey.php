<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ManageEtherOneKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:manageEtherOneKey';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (Cache::has('etherOneATToken')){
            print_r('Token Exists'.Cache::get('etherOneATToken')."\n");
            return Cache::get('etherOneATToken');
        }else{
            Log::log('info', 'Calling EtherOne Not Existent getting Token');
            $headers = [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ];
            $appKey = 'fe53389e-dc9c-499e-8827-34a7a932c268';
            $appSecret = 'be5446ff-9073-4d01-8362-87737c77248d';

            $response = Http::withHeaders($headers)
                ->post('https://openapi.etheroneafrica.com/api/v1/authenticate', [
                    'appKey' => $appKey,
                    'appSecret' => $appSecret,
                ]);
            //Save token to cache
            Cache::put('etherOneATToken', $response->body(), now()->addSeconds(340));
            Log::log('info', 'Token Saved to Cache');

        }
    }
}
