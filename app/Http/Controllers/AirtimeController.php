<?php

namespace App\Http\Controllers;

use App\Models\Airtime;
use Illuminate\Http\Request;

class AirtimeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $airtime = Airtime::orderBy('id','desc')->paginate(50);
        return response()->json($airtime);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Airtime  $airtime
     * @return \Illuminate\Http\Response
     */
    public function show(Airtime $airtime)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Airtime  $airtime
     * @return \Illuminate\Http\Response
     */
    public function edit(Airtime $airtime)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Airtime  $airtime
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Airtime $airtime)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Airtime  $airtime
     * @return \Illuminate\Http\Response
     */
    public function destroy(Airtime $airtime)
    {
        //
    }

    public function updateStatus(Request $request)
    {
        $data = $request->all();
        $airtime = Airtime::where('responseId',$data['requestId'])->first();
        if($airtime){
            $airtime->status = $data['status'];
            $airtime->response = $data['description'];
            $airtime->save();
        }
        return response()->json(['status'=>'success']);
    }

    public function searchAirtime($phone)
    {
        $airtime = Airtime::where('msisdn',$phone)->orderBy('id','desc')->paginate(50);
        return response()->json($airtime);
    }

    public function receiveEtherOneCallBack(Request $request){
        //Write response to the Log File
        $logFile = 'etherOneCallBack.log';
        $file = storage_path('logs/'.$logFile);
        $log = fopen($file, 'a');
        fwrite($log, $request->getContent());
        fclose($log);
    }
}
