<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Airtime;
use Illuminate\Support\Str;

class ProcessFailedTATransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:processFailedTAAirtimeTransactions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process Failed TA Airtime Transactions';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //Get Failed Transactions whith channel True African and status Failed and response is Make Payment Error: Msisdn can not be recharged within 3 minute interval and update_at is greater than 5 minutes
        $transactions = Airtime::where('channel','True African')->where('status','Failed')
        ->where('response','Make Payment Error: Msisdn can not be recharged within 3 minute interval')
        ->whereDate('updated_at','<=',Carbon::now()->subMinutes(5)->toDateTimeString())
        ->limit(5)->oldest()->get();
        
        foreach($transactions as $transaction){
            //Reset Transaction to be picked up by the scheduler
            $transaction->status = 'pending';
            $transaction->transactionId= Str::uuid();
            $transaction->nextAttempt = date('Y-m-d H:i:s',strtotime('+5 minutes'));
            $transaction->save();
           
        }
    }

   
}
