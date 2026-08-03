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
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InMessagesController extends Controller
{
    public $airtime = 5000; //amount to be sent
    public $isActive = true;
    public $winnerRate = 1; //Everyone wins 5000

    /************MESSAGE TEMPLATES**************/
    public $airtimeWinnerMessage = 'Congrats! You have won instant Airtime in the Pepsi Drink and Win promo. It will be credited to your phone shortly. Ts n Cs apply.';
    public $invalidCodeMessage = 'Thanks for participating in the Pepsi Drink and Win promo. This code is invalid. Please check the bottle cap and try again or contact your Pepsi agent';
    public $alreadyUsedMessage = 'Thanks for participating in the Pepsi Drink and Win promo. This code has already been captured. Try another one or contact your local Pepsi agent.';
    public $closed = 'Thank you for taking part in the Pepsi Drink and Win promotion. It ended on 11 Dec 2022. Look out for more exciting offers from Pepsi. Ts n Cs apply';
    public $blocked = 'Thank you for choosing Pepsi. You are not eligible to participate in this Promo. See Ts and Cs on www.pepsi.co.ug.';
    public $notStarted = 'Thank you for taking part in the Pepsi Drink and Win promotion. It starts on 1 October 2023. Look out for more exciting offers from Pepsi. Ts n Cs apply';
    public $unsupportedNetwork = 'Thank you for choosing Pepsi. This promotion is not supported on your network. Ts n Cs apply';


    public function receiveMessages(Request $request)
    {
        $msisdn = $request->input('msisdn');
        $text = $request->input('text');

        Log::info('Received message from: ' . $msisdn . ' with message: ' . $text);

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
            if (str_starts_with($message->msisdn, '25671')) {
                $response = $this->unsupportedNetwork;
            } else {
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
                            $amount = $this->airtime;
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
                } else {
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

    public function sendMessage($msisdn, $message, $inMessageId = null)
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
    public function sendMessageWithAT($msisdn, $message)
    {
        $username = env('AT_USERNAME');
        $apiKey = env('AT_API_KEY');
        $AT = new AfricasTalking($username, $apiKey);

        $sms = $AT->sms();
        // Set the numbers you want to send to in international format
        $recipients = '+' . $msisdn;

        // Set your shortCode or senderId
        $from = 'Plascon';

        try {
            // Thats it, hit send and we'll take care of the rest
            $result = $sms->send([
                'to' => $recipients,
                'message' => $message,
                'from' => $from,
            ]);
            return $result;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    /***********************************
     * Send Message With EtherOne
     */
    public function sendMessageWithEtherOne($msisdn, $message, $messageId)
    {
        $AuthDetails = $this->getToken();
        $token = json_decode($AuthDetails)->access_token;

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
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
    private function getToken()
    {
        if (Cache::has('etherToken')) {
            return Cache::get('etherToken');
        } else {
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

        $inMsgCount = InMessages::count();
        $validInMsgCount = InMessages::where('status', 'valid')->count();
        $airtimeWinnerSum = Airtime::sum('amount');


        $data['codes'] = number_format(Codes::count());
        $data['valid_codes'] = number_format($validInMsgCount);
        $data['airtime'] = number_format(10000000);
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

    public function getChart(Request $request)
    {
        $days = (int) $request->get('days', 7);
        if (!in_array($days, [7, 30])) {
            $days = 7;
        }

        $dates = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $dates[] = now()->subDays($i)->format('Y-m-d');
        }

        $startDate = now()->subDays($days - 1)->startOfDay();

        $results = DB::table('in_messages')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        $counts = [];
        foreach ($dates as $date) {
            $counts[] = $results[$date] ?? 0;
        }

        return response()->json([
            'dates' => $dates,
            'counts' => $counts
        ]);
    }


    public function getAreaChart()
    {

        //Check if cached data exists
        if (Cache::has('areaChart')) {
            $data = Cache::get('areaChart');
            return response()->json($data);
        } else {

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
            while ($startDate->lte($endDate)) {
                $date = $startDate->format('Y-m-d');
                array_push($dates, $date);
                $count = InMessages::whereDate('created_at', $date)->count();
                array_push($counts, $count);
                $startDate->addDay();
            }
            $data['dates'] = $dates;
            $data['counts'] = $counts;

            return response()->json($data);
        }


    }


    public function getInMessagesReport(Request $request)
    {
        $from = $request->fromDate;
        $to = $request->todate;
        $status = $request->status;

        $from = Carbon::parse($from)->startOfDay();
        $to = Carbon::parse($to)->endOfDay();

        $messages = DB::table('in_messages')
            ->whereBetween('created_at', [$from, $to]);

        if ($status && $status != 'Any') {
            $messages = $messages->where('status', $status);
        }

        $messages = $messages->get();

        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=in-messages-report.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        $columns = array('ID', 'PHONE NUMBER', 'MESSAGE TEXT', 'STATUS', 'RESPONSE SENT', 'DATE');

        $callback = function () use ($messages, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($messages as $msg) {
                fputcsv($file, array($msg->id, $msg->msisdn, $msg->inText, $msg->status, $msg->response ?? '', $msg->created_at));
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

}
