<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Airtime;
use AfricasTalking\SDK\AfricasTalking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SendAirtime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:sendAirtime';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Airtime to winners';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $now = Carbon::now();

        $transactions = Airtime::where('status','Pending')
            ->where('channel','!=','EtherOne')
            ->whereDate('nextAttempt','<=',$now->toDateTimeString())
            ->limit(20)->get();

        foreach($transactions as $transaction){
            //update status to picked
            $transaction->status = 'picked';
            $transaction->save();
            //send airtime
            $response = $this->sendAirtime($transaction->msisdn,$transaction->amount,$transaction->transactionId,$transaction->channel);

            if($response['status'] == 'Retry'){
                $transaction->status = 'Pending';
                $transaction->nextAttempt = date('Y-m-d H:i:s',strtotime('+5 minutes'));
            }else{
                $transaction->status = $response['status'];
            }
            $transaction->response = $response['message'];
            $transaction->responseId = $response['requestId'];
            $transaction->channel = $response['channel'];
            $transaction->save();
        }
    }

    public function sendAirtime($msisdn,$amount,$uuid,$channel){
        if($channel == "Africa\'s Talking" || $channel == "Africa's Talking"){
            //Call Africa's Talking API
            return $this->sendAirtimeUsingAfricaIsTalking($msisdn,$amount,$uuid);
        }
        elseif($channel == 'EtherOne'){
            //Call EtherOne API
            //return $this->sendAirtimeUsingEtherOne($msisdn, $amount, $uuid);
         }
        else {
            return [
                "status" => "Failed",
                "requestId" => NULL,
                "channel" => "Unknown",
                "message" => "Unknown Channel"
            ];
        }


    }


    //Get Network
    public function getNetwork($msisdn){
        $network = 'MTN';
        $sub = substr($msisdn,0,5);
        if(in_array($sub,['25677','25678','25676','25639']))
            $network = 'MTN';
        elseif(in_array($sub,['25674','25670','25675']))
            $network = 'AIRTEL';
        else
            $network = 'UNKNOWN';

        return $network;
    }

    public function sendAirtimeUsingAfricaIsTalking($msisdn,$amount,$uuid)
    {
        // Set your app credentials
       # $username = "sandbox";
       # $apiKey   = "5b37a9da43f359fd89f8f5e8bbff915cd171fda6a57a9327b247d792fef9501b";

        $username = env('AT_USERNAME');
       $apiKey   = env('AT_API_KEY');

        // Initialize the SDK
        $AT = new AfricasTalking($username, $apiKey);

        // Get the airtime service
        $airtime = $AT->airtime();

        // Set the phone number, currency code and amount in the format below
        $recipients = [[
            "phoneNumber"  => $msisdn,
            "currencyCode" => "UGX",
            "amount"       => $amount
        ]];
        $options = [  "idempotencyKey" => $uuid];

        try {
            // That's it, hit send and we'll take care of the rest
            $results = $airtime->send([
                "recipients" => $recipients
            ],$options);

            if($results['status'] == "success"){
                $data = $results['data'];
                if($data->errorMessage == "None"){
                    $response = [
                        "status" => "Sent",
                        "requestId" => $data->responses[0]->requestId,
                        "channel" => "Africa's Talking",
                        "message" => ""
                    ];
                }else{
                    $response = [
                        "status" => "Failed",
                        "requestId" => NULL,
                        "channel" => "Africa's Talking",
                        "message" => $data->errorMessage
                    ];
                }
            }
            else{
                $response = [
                    "status" => "Failed",
                    "requestId" => NULL,
                    "channel" => "Africa's Talking",
                    "message" => $results['data']->errorMessage
                ];
            }
            return $response;
        } catch(Exception $e) {
            $response = [
                "status" => "Failed",
                "requestId" => NULL,
                "channel" => "Africa's Talking",
                "message" => $e->getMessage()
            ];
            return $response;
        }

        return 0;
    }


    public function sendAirtimeUsingEtherOne($msisdn,$amount,$uuid){
//        $AuthDetails = $this->getToken();
//        $token = json_decode($AuthDetails)->access_token;
//
//        $data = $msisdn."&".$uuid."&".$amount;
//
//        $private_key = openssl_pkey_get_private(storage_path('app/keys/private.pem'));
//        $signature = openssl_sign($data, $signature, $private_key, OPENSSL_ALGO_SHA256);
//        $signature = base64_encode($signature);
//
//        $headers = [
//            'Content-Type' => 'application/json',
//            'Accept' => 'application/json',
//            'Authorization' => 'Bearer '.$token,
//        ];
//
//        $response = Http::withHeaders($headers)
//            ->post('https://openapi.etheroneafrica.com/api/v1/airtime/request', [
//                'msisdn' => $msisdn,
//                'transactionID' => $uuid,
//                'narration' => "Airtime Request",
//                'amount' => $amount,
//                'signature' => $signature
//            ]);
//
//        $responseData = json_decode($response->body());
//
//        if($responseData->code == "201") {
//            $resp = [
//                "status" => "Sent",
//                "requestId" => $responseData->transactionID,
//                "channel" => "EtherOne",
//                "message" => $responseData->message
//            ];
//        }else{
//            $resp = [
//                "status" => "Failed",
//                "requestId" => NULL,
//                "channel" => "EtherOne",
//                "message" => $responseData->message
//            ];
//        }
//        return $resp;

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
            $appKey = env('ETHERONE_APP_KEY');
            $appSecret = env('ETHERONE_APP_SECRET');

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
