<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use App\Models\Airtime;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class EtherOneAirtimeProcessor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:sendAirtimeEtherOne';

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
        $now = Carbon::now();

        $transactions = Airtime::where('status','Pending')->where('channel','EtherOne')
            ->whereDate('nextAttempt','<=',$now->toDateTimeString())
            ->limit(50)
            ->orderBy('id','desc')
            ->get();
        foreach($transactions as $transaction){
            print_r($transaction->id." ".$transaction->msisdn." ".$transaction->amount."\n");
            $response = $this->sendAirtimeUsingEtherOne($transaction->msisdn,$transaction->amount,$transaction->transactionId);
            $response = json_decode($response);
            print_r($response);
            //Check if response has error
            if (isset($response->error)){
                //Run Artisan command to clear cache
                Artisan::call('cache:clear');
                //Update transaction status to failed
                $transaction->status = 'Pending';
                $transaction->nextAttempt = Carbon::now()->addMinutes(10)->toDateTimeString();
                $transaction->response = $response->error;
                $transaction->save();
            }else{
                //Check if response has status
                if($response->code == 201){
                    //Update transaction status to success
                    $transaction->status = 'Success';
                    $transaction->responseId = $response->transactionID;
                    $transaction->response = $response->message;
                    $transaction->save();
                }
                elseif ($response->code == 500 || $response->code == 401){
                    $transaction->status = 'Pending';
                    $transaction->response = $response->message;
                    $transaction->nextAttempt = Carbon::now()->addMinutes(10)->toDateTimeString();
                    $transaction->save();
                }
                elseif ($response->code == 203){
                    $transaction->status = 'Success';
                    $transaction->responseId = $response->transactionID;
                    $transaction->response = $response->message;
                    $transaction->save();
                }
                else{
                    //Update transaction status to failed
                    $transaction->status = 'Failed';
                    $transaction->response = $response->message;
                    $transaction->save();
                }
            }

        }

    }

    public function sendAirtimeUsingEtherOne($msisdn,$amount,$uuid){
        $AuthDetails = $this->getToken();
        $data = json_decode($AuthDetails);
        $token = $data->access_token;
        //Check if token has expired
        $now = Carbon::now();
        $expiresAt = $data->expires_in;
        //print_r("Expires At: ".$expiresAt."\n");
        //print_r("Token: ".$token."\n");
        $expiresAt = Carbon::parse($expiresAt);

        if ($now->greaterThan($expiresAt)){
            //Token has expired
            print_r("Token has expired"."\n");
            //Get new token
            $AuthDetails = $this->getToken();
            $data = json_decode($AuthDetails);
            $token = $data->access_token;
        }

        if($token == null){
            return [
                "status" => "Failed",
                "message" => "Token is null",
                "requestId" => $uuid,
                "channel" => "EtherOne"
            ];
        }else{
            $headers = [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$token,
            ];

            $response = Http::withHeaders($headers)
                ->post('https://openapi.etheroneafrica.com/api/v1/airtime/request', [
                    'msisdn' => $msisdn,
                    'transactionID' => $uuid,
                    'narration' => "Airtime Request",
                    'amount' => (float)$amount
                ]);
            return $response->body();
        }

    }

    private function getToken()
    {
        $token = Cache::get('EtherAirtimeToken');
        if ($token == null){
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
            Cache::put('EtherAirtimeToken', $response->body(), now()->addSeconds(480));
            $token = $response->body();
        }

        return $token;
    }
}
