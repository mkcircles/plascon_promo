<?php

namespace App\Console\Commands;
use App\Models\Airtime;
use Illuminate\Console\Command;

class CheckAirtimeStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:checkStatus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Airtime Status';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //get 10 sent transactions that are more than 5 minutes old 
        $transactions = Airtime::where(['status'=>'Sent','channel'=>'True African'])->whereDate('updated_at','<=',date('Y-m-d H:i:s',strtotime('-5 minutes')))->limit(20)->get();
        foreach($transactions as $transaction){
            $response = $this->checkStatus($transaction);
            if($response['status'] == 'Retry'){
                $transaction->status = 'Sent';
                $transaction->nextAttempt = date('Y-m-d H:i:s',strtotime('+5 minutes'));
            }else{
                $transaction->status = $response['status'];
            }
            $transaction->response = $response['message'];
            $transaction->save();
        }
        return 0;
    }

    public function checkStatus($transaction){   
        //send airtime using True Africans API
        $airtimeUsername = 'plascon';
        $airtimePassword = 'Pkasvgn37';
        #$airtimeUrl = 'http://nickel.trueafrican.com/evolt/apitest.php';
        $airtimeUrl = "http://nickel.trueafrican.com/airtime/pinless/api.php";

        $data = "<?xml version='1.0' encoding='UTF-8'?>
            <request>
                <username>".$airtimeUsername."</username>
                <password>".$airtimePassword."</password>
                <method>checkStatus</method>
                <msisdn>".$transaction->msisdn."</msisdn>
                <requestId>".$transaction->responseId."</requestId>
            </request>";
        $headers =['Content-Type: text/xml','Content-transfer-encoding: text'];

        //Make Request to True African API
        //Make API Call
        $curl = curl_init();
        // Set some options - we are passing in a user agent too here
        curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $airtimeUrl,
        CURLOPT_USERAGENT => 'Plascon',
        CURLOPT_POST => 1,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POSTFIELDS => $data
        ));

        $resp = curl_exec($curl);
        curl_close($curl);

        $oXML = simplexml_load_string($resp);
        //check if response code is 200
        if($oXML->code == '200'){
            $response = [
                "status" => "Success",
                'message' => "Airtime Sent Successfully"
            ];
        }
        elseif(in_array($oXML->code,['304','303','302','301'])){
            $response = [
                "status" => "Failed",
                'message' => $oXML->message
            ];
        }else{
            $response = [
                "status" => "Retry",
                'message' => $oXML->message
            ];
        }
           
        
        
        return $response;
    }
}
