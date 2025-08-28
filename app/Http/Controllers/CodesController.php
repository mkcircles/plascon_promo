<?php

namespace App\Http\Controllers;

use App\Models\Codes;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CodesController extends Controller
{
    public function generateCodes(Request $request){
        $area = $request->area;
        $count = $request->count;

        $prefix = $this->getPrefix($area);
        // dd($prefix);
        $code = $prefix['code']; 
        $area = $prefix['area'];

        for ($i = 0; $i < $request->count; $i++) {
            if( strlen($code)== 3){   //Generate 6 Digit Code
                $data = $this->createCode(5);
            }elseif( strlen($code)== 4){    //Generate 4 Digit Code
                $data = $this->createCode(4);
            }
            $finalCode = $code.''.$data;
            if($this->checkExistance($finalCode)){
                echo $i.' => '.$finalCode.' :: '.$area.'<br/>';
                $this->saveCode($finalCode,$area);
            }
        }
        $count = Codes::where('area',$area)->count();
        $balance = $request->count - $count;
        for ($j = 0; $i < $balance; $j++) {
            if( strlen($code)== 2){   //Generate 6 Digit Code
                $data = $this->createCode(6);
            }elseif( strlen($code)== 4){    //Generate 4 Digit Code
                $data = $this->createCode(4);
            }
            $finalCode = $code.''.$data;
            if($this->checkExistance($finalCode)){
                $this->saveCode($finalCode,$area);
            }
        }

    }


    private function getPrefix($area)
    {
        switch($area){
            case 'Jinja':{ return $data = ['code'=>'SMKJ', 'area'=>'Jinja']; break; }
            //case 'Iganga':{ $data = ['code'=>'KP23', 'area'=>'Iganga']; break; }
            case 'Mbale':{ return $data = ['code'=>'SMKM', 'area'=>'Mbale']; break; }
            //case 'Soroti':{ $data = ['code'=>'KP25', 'area'=>'Soroti']; break; }
            case 'Lira':{ return $data = ['code'=>'SMKL', 'area'=>'Lira']; break; }
            case 'Gulu':{ return $data = ['code'=>'SMKG', 'area'=>'Gulu']; break; }
            case 'Arua':{ return $data = ['code'=>'SMKA', 'area'=>'Arua']; break; }
            //case 'Masindi':{ return $data = ['code'=>'KP29', 'area'=>'Masindi']; break; }
            //case 'Hoima':{ return $data = ['code'=>'KP42', 'area'=>'Hoima']; break; }
            case 'Fort':{ return $data = ['code'=>'SMKF', 'area'=>'Fort Portal']; break; }
            case 'Mbarara':{ return $data = ['code'=>'SMKR', 'area'=>'Mbarara']; break; }
            case 'Masaka':{ return $data = ['code'=>'SMKS', 'area'=>'Masaka']; break; }
            case 'Test':{ return $data = ['code'=>'SMKZ', 'area'=>'Test']; break; }
            default: { return $data = ['code'=>'SMK', 'area'=>'Kampala']; break;}
         
        }
       
    }

    private function createCode($int)
    {
        $characters = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $int; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * Check if Code already exists
     * @param $finalCode
     * @return bool
     */
    private function checkExistance($finalCode)
    {
        $code = Codes::where('code',$finalCode)->first();
        return is_null($code)?true:false;
    }

    private function saveCode($finalCode, $area)
    {
        $mytime = Carbon::now()->toDateTimeString();

        Codes::insert([
            'code'=>$finalCode,
            'area'=>$area,
            'status'=>'Pending',
            'inMessageId'=>'',
            'prizeWon' =>'',
            'created_at'=>$mytime
        ]);

        return;
    }

    public function getCodes(){
        $codes = Codes::orderBy('id','desc')->paginate(50);
        return response()->json($codes);
    }

    public function getAreaCodes($name){
        $name == 'fort_portal' ? $name = 'Fort Portal' : $name;
        $codes = Codes::where('area',$name)->orderBy('id','desc')->paginate(50);
        return response()->json($codes);
    }

    public function getUsedCodes(){
        $codes = Codes::where('status','used')->paginate(50);
        return response()->json($codes);
    }

    public function getCodeData(){
        $codes = Codes::paginate(100);
        return Datatables::of($codes)->make(true);
    }

    public function searchCode($query){
        $codes = Codes::where('code',$query)->orderBy('id', 'desc')->paginate(100);
        return Datatables::of($codes)->make(true);
    }


    public function export(Request $request)
    {
        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=file.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        $codes = Codes::getReviewExport($this->hw->healthwatchID)->get();
        $columns = array('Code', 'Area', 'Status', 'Phone Number', 'Prize Won');

        $callback = function() use ($codes, $columns)
        {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach($codes as $code) {
                fputcsv($file, array($code->code, $code->area, $code->status, $code->inMessageId, $code->prizeWon));
            }
            fclose($file);
        };
        return Response::stream($callback, 200, $headers);
    }



    public function getCodesReport(Request $request){
        $from = $request->fromDate; $to = $request->todate;
        $prize = $request->prize;
        $status = $request->status;
        $area = $request->area;

        $codes = DB::table('codes')->whereBetween('updated_at', [$from, $to]);
        if($prize != 'Any'){
            if($prize == 'pen'){ $p = 'Pen'; }else{ $p = 'Airtime - 2000'; }
            $codes = $codes->where('prizeWon',$p);
        }
        if($area != 'all'){
            $codes =$codes->where('area',$area);
        }
        $codes = $codes->get();
       // dd($codes);
        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=file.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );
        $columns = array('PHONE NUMBER','CODE', 'STATUS', 'PRIZE WON','DATE');

        $callback = function() use ($codes, $columns)
        {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach($codes as $code) {
                fputcsv($file, array($code->inMessageId,$code->code,$code->status,$code->prizeWon,$code->updated_at));
            }
            fclose($file);
        };
        return Response::stream($callback, 200, $headers);
    }
}
