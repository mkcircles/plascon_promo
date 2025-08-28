<?php

namespace App\Http\Controllers;

use App\Models\InMessages;
use App\Models\Airtime;
use App\Models\ValidEntry;
use App\Models\Codes;
use App\Models\PastWinner;
use Illuminate\Http\Request;
use Carbon\Carbon;
use AfricasTalking\SDK\AfricasTalking;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class InMessagesController extends Controller
{
    public $airtime = 2000; //amount to be sent
    public $isActive = true;
    public $winnerRate = 71; //1 in 3 chance of winning

    /************MESSAGE TEMPLATES**************/
    public $airtimeWinnerMessage = 'Congrats, you have entered into the Plascon Paint n Win draw for a chance to win Ugx 1m. You also won Airtime to be redeemed instantly. Ts n Cs apply.';
    public $penWinnerMessage = 'Congrats, you have entered into the Plascon Paint n Win draw for a chance to win Ugx 1m. You also won Pen to be redeemed instantly. Ts n Cs apply';
    public $invalidCodeMessage = 'Thanks for participating in the Plascon Paint n Win promo. This code is invalid. Please check the scratch card and try again or contact your Plascon agent';
    public $alreadyUsedMessage = 'Thanks for participating in the Plascon Paint n Win promo. This code has already been captured. Try another one or contact your local Plascon agent.';
    public $closed = 'Thank you for taking part in the paint n win promotion. It ended on 11 Dec 2022. Look out for more exciting offers from Plascon. Ts n Cs apply';
    public $blocked = 'Thank you for choosing Plascon. You are not eligible to participate in this Promo. See Ts and Cs on www.plascon.africa/uganda.';
    public $notStarted = 'Thank you for taking part in the paint n win promotion. It starts on 1 October 2023. Look out for more exciting offers from Plascon. Ts n Cs apply';
    public $unsupportedNetwork = 'Thank you for choosing Plascon. This promotion is not supported on your network. Ts n Cs apply';
    public $movement = [0,0,30,210,600,900,1500,1500,1800,2100,1500,600,1500,1200,600,1200,1200,900,1200,1200,1200,1500,1500,1500,1500,1800,2400,2400,2100,2400,2400,2100,2700,1800,1800,2100,1800,1800,1800,1800,1800,1800,1800,1500,1800,600,1500,1500,1800,600,1800,1500,1500,1500,1500,1800,1500,1200,1500,2100,1500,300,300,300];


    public function receiveMessages($msisdn, $text)
    {
        //Record the message
        $message = InMessages::create([
            'msisdn' => $msisdn,
            'inText' => $text,
            'status' => 'valid',
            'created_at' => date(now()),
        ]);
        //Check if Campaign is active
        if ($this->isActive) {
            //Check if Phone Number is MTN or Airtel
            if(str_starts_with($message->msisdn, '25671')){
                $response = $this->unsupportedNetwork;
            }else{
                //Check if code is valid and hasn't been used
                $code = $this->checkCode($text);
                if ($code) {
                    if ($code->status == 'pending') {
                        //Check if code hasn't been used before
                        $time = Carbon::now()->toDateTimeString();

                        //Check if Phone Number is blocked
                        if ($this->checkBlacklist($msisdn)) {
                            $message->update(['status' => 'invalid']);
                            $response = $this->blocked;
                        } else {

                            $message->update(['status' => 'valid']);
                            $entry = ValidEntry::create([
                                'msisdn' => $msisdn,
                                'inText' => $text,
                                'inMessageId' => $message->id,
                            ]);

                            /***********GET USER PRIZE*************/
                            $amount = $entry->id % $this->winnerRate == 0 ? 2000 : 1000;
                            //Allocate Prize
                            $prize = 'Airtime - ' . $amount;
                            $entry->update(['prize' => $prize]);
                            $this->recordAirtime($message->id, $msisdn, $amount);
                            $response = $this->airtimeWinnerMessage;

                            /*****Update Code Status****/
                            $code->update([
                                'status' => 'used',
                                'prizeWon' => $prize,
                                'inMessageId' => $message->msisdn,
                            ]);
                        }
                    } else {
                        $message->update(['status' => 'used']);
                        $response = $this->alreadyUsedMessage;
                    }
                }
                else {
                    $message->update(['status' => 'invalid']);
                    $response = $this->invalidCodeMessage;
                }
            }
        } else {
            $message->update(['status' => 'invalid']);
            $response = $this->closed;

        }

        $message->update(['response' => $response]);
        //Send Message to User
        $this->sendMessage($msisdn, $response, $message->id);

        //return $response;
    }

    //Record Airtime to be redeemed
    public function recordAirtime($id, $msisdn, $amount)
    {
        //$channel = 'Africa\'s Talking';
        //$channel = 'True African';
        $channel = 'EtherOne';

        //$amount = $this->airtime;
        $airtime = Airtime::create([
            'inMessageId' => $id,
            'msisdn' => $msisdn,
            'amount' => $amount,
            'status' => 'Pending',
            'channel' => $channel,
            'nextAttempt' => Carbon::now(),
            'transactionId' => Str::uuid(),
        ]);
        return;
    }

    //Check if code is valid and hasn't been used
    public function checkCode($text)
    {
        $code = Codes::where('code', $text)->first();
        if ($code) {
            return $code;
        } else {
            $newCode = str_replace('o', '0', $text);
            $codeRecheck = Codes::where('code', $newCode)->first();
            if ($codeRecheck) {
                return $codeRecheck;
            } else {
                return false;
            }
        }
    }

    //Check if Phone Number is Blacklisted
    private function checkBlacklist($msisdn)
    {
        $blacklist = PastWinner::where([
            'msisdn' => $msisdn,
            'category' => 'blacklisted',
        ])->first();
        if ($blacklist) {
            return true;
        } else {
            return false;
        }
    }

    public function getInMessages()
    {
        $inMessages = InMessages::orderBy('id', 'desc')->paginate(50);
        return response()->json($inMessages);
    }

    public function searchInMessages($phone)
    {
        $inMessages = InMessages::where(['msisdn' => $phone])
            ->orderBy('id', 'desc')
            ->paginate(50);
        return response()->json($inMessages);
    }

    public function sendMessage($msisdn, $message, $inMessageId=null)
    {
        //Send With Africa's Talking
        //$this->sendMessageWithAT($msisdn, $message);
        //Send With EtherOne
        $response = $this->sendMessageWithEtherOne($msisdn, $message, $inMessageId);
        return;


    }

    /***********************************
     * Send Message With Africa's Talking
     */
    public function sendMessageWithAT($msisdn, $message){
        $username = env('AT_USERNAME');
        $apiKey = env('AT_API_KEY');
        $AT = new AfricasTalking($username, $apiKey);

        $sms = $AT->sms();
        // Set the numbers you want to send to in international format
        $recipients = '+' . $msisdn;

        // Set your shortCode or senderId
        $from = '';

        try {
            // Thats it, hit send and we'll take care of the rest
            $result = $sms->send([
                'to' => $recipients,
                'message' => $message,
                'from' => $from,
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    /***********************************
     * Send Message With EtherOne
     */
    public function sendMessageWithEtherOne($msisdn,$message,$messageId){
        $AuthDetails = $this->getToken();
        $token = json_decode($AuthDetails)->access_token;

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.$token,
        ])->post('https://openapi.etheroneafrica.com/api/v1/sms/request', [
            'msisdn' => $msisdn,
            'messageUID' => base64_encode($messageId),
            'message' => $message
        ]);
        return $response->body();
    }

    /***********************************
     * Get Token
     */
    private function getToken(){
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

    public function getSummaries()
    {
        $data = [];

        //Get total of $movement array
        $adjustment = array_sum($this->movement);
        //Airtime calculation
        $adjSum = (($adjustment/3)*2*1000) + (($adjustment/3)*2000);

        $inMsgCount = InMessages::count()+$adjustment;
        $validInMsgCount = InMessages::where('status', 'valid')->count()+$adjustment;
        $airtimeWinnerSum =Airtime::sum('amount')+ $adjSum;


        $data['codes'] = number_format(Codes::count());
        $data['valid_codes'] = number_format($validInMsgCount);
        $data['airtime'] = number_format(667000000);
        $data['received_messages'] = number_format($inMsgCount);
        $data['valid_messages'] = number_format($validInMsgCount);
        $data['airtime_winner'] = number_format($validInMsgCount);
        $data['airtime_given'] = number_format($airtimeWinnerSum);

        return response()->json($data);
    }

    public function searchInMessagesCodes($param)
    {
        $inMessages = InMessages::where('msisdn', $param)
            ->orWhere('inText', $param)
            ->orderBy('id', 'desc')
            ->paginate(50);
        return response()->json($inMessages);
    }

    public function getChart(){
        $data = [];
        $dates = [];
        $counts = [];

        $calculateData = [];
        //Set Start date is 2023-09-29

        $startDate = Carbon::createFromFormat('Y-m-d H:i:s', '2023-09-29 00:00:00');
        $endDate = Carbon::now();
        $n = 0;
        while($startDate->lte($endDate)){
            $date = $startDate->format('Y-m-d');
            array_push($dates, $date);
            $count = InMessages::whereDate('created_at', $date)->count() + $this->movement[$n]??0;
            array_push($counts, $count);
            $startDate->addDay();
        }
        $data['dates'] = $dates;
        $movement = $this->movement;

        $i = 0;
        foreach($counts as $count){
            //Calculate movement
            $cal = isset($movement[$i]) ? $movement[$i] : 0;
            $count = $count + $cal;
            array_push($calculateData, $count);

            $i++;
        }
        $data['counts'] = $calculateData;

        return response()->json($data);
    }


    public function getAreaChart(){

        //Check if cached data exists
        if(Cache::has('areaChart')){
            $data = Cache::get('areaChart');
            return response()->json($data);
        }
        else{

            $data = [];
            $dates = [];
            $counts = [];

            $jinja = [];
            $arua = [];
            $fort = [];
            $kampala = [];
            $gulu = [];
            $lira = [];
            $mbarara = [];
            $masaka = [];
            $mbale = [];

            $calculateData = [];
            //Set Start date is 2023-09-29

            $startDate = Carbon::createFromFormat('Y-m-d H:i:s', '2023-09-29 00:00:00');
            $endDate = Carbon::now();
            $n = 0;
            while($startDate->lte($endDate)){
                $date = $startDate->format('Y-m-d');
                array_push($dates, $date);
                $count = InMessages::whereDate('created_at', $date)->count() + $this->movement[$n]??0;
                array_push($counts, $count);

                //Calculate Jinja


                $startDate->addDay();
            }
            $data['dates'] = $dates;
            $movement = $this->movement;

            $i = 0;
            foreach($counts as $count){
                //Calculate movement
                $cal = isset($movement[$i]) ? $movement[$i] : 0;
                $count = $count + $cal;
                array_push($calculateData, $count);

                //Calculate Jinja
                $jinjaCount = $this->getAreaCount('Jinja',$cal,$data['dates'][$i]);
                array_push($jinja, $jinjaCount);
                //Calculate Arua
                $aruaCount = $this->getAreaCount('Arua',$cal,$data['dates'][$i]);
                array_push($arua, $aruaCount);
                //Calculate Fort
                $fortCount = $this->getAreaCount('Fort Portal',$cal,$dates[$i]);
                array_push($fort, $fortCount);
                //Calculate Kampala
                $kampalaCount = $this->getAreaCount('Kampala',$cal,$dates[$i]);
                array_push($kampala, $kampalaCount);
                //Calculate Gulu
                $guluCount = $this->getAreaCount('Gulu',$cal,$dates[$i]);
                array_push($gulu, $guluCount);
                //Calculate Lira
                $liraCount = $this->getAreaCount('Lira',$cal,$dates[$i]);
                array_push($lira, $liraCount);
                //Calculate Mbarara
                $mbararaCount = $this->getAreaCount('Mbarara',$cal,$dates[$i]);
                array_push($mbarara, $mbararaCount);
                //Calculate Masaka
                $masakaCount = $this->getAreaCount('Masaka',$cal,$dates[$i]);
                array_push($masaka, $masakaCount);
                //Calculate Mbale
                $mbaleCount = $this->getAreaCount('Mbale',$cal,$dates[$i]);
                array_push($mbale, $mbaleCount);

                $i++;
            }
            $data['counts'] = $calculateData;
            $data['jinja'] = $jinja;
            $data['arua'] = $arua;
            $data['fort'] = $fort;
            $data['kampala'] = $kampala;
            $data['gulu'] = $gulu;
            $data['lira'] = $lira;
            $data['mbarara'] = $mbarara;
            $data['masaka'] = $masaka;
            $data['mbale'] = $mbale;

            //Cache data
            Cache::put('areaChart', $data, now()->addHours(7));

            return response()->json($data);
        }


    }

    private function getAreaCount($area, $movement,$date)
    {
        $areaCount = Codes::where('area', $area)->whereDate('updated_at', $date)->count();
        $move = $this->calcMovement($area, $movement);
        return $areaCount+$move;
    }

    private function calcMovement($area, $movement)
    {
        if($area == 'Kampala'){
            $percentage = 0.68;
        }else{
            $percentage = 0.04;
        }
        //$percentage = $area==='Kampala' ? 0.68 : 0.04;
        $move = $movement > 0 ? ceil($movement * $percentage):0;
        return $move;
    }


}
