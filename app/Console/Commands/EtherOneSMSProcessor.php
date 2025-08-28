<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class EtherOneSMSProcessor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:processSMS';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send SMS using EtherOne API';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $AuthDetails = $this->getToken();
        $token = json_decode($AuthDetails)->access_token;

        $msisdn = "256781456492";
        $messageUID = "6cc568d3-285a-4ea1-a58a-5b89e4ead775";
        $message = "Test Message";

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
                'Authorization' => 'Bearer '.$token,
            ])->post('https://openapi.etheroneafrica.com/api/v1/sms/request', [
            'msisdn' => $msisdn,
            'messageUID' => $messageUID,
            'message' => $message
        ]);

        dd($response->body());

    }

    private function getToken()
    {
        if (Cache::has('etherToken')){
            return Cache::get('etherToken');
        }else{
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
            Cache::put('etherToken', $response->body(), now()->addSeconds(340));
            return $response->body();
        }
    }
}













