<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Http;

class generateCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:generateCodes';

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
        $this->generateCodes();
    }

    public function generateCodes(){
        $i=0;
        while($i<300){
            $json = json_decode(file_get_contents('https://sms-promotion.test/codes/generate/Test/100'), true);
            sleep(15);
            $i++;
        }

    }
}
