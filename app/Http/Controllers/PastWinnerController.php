<?php

namespace App\Http\Controllers;

use App\Models\PastWinner;
use Illuminate\Http\Request;
use auth;

class PastWinnerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($status)
    {
        $pastWinners = PastWinner::where(['category'=>$status])->orderBy('id','desc')->paginate(50);
        return response()->json($pastWinners);
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
        $pastWinner = PastWinner::create([
            'msisdn' => $request->msisdn,
            'addedBy' => auth('sanctum')->user()->id,
            'category' => $request->category
        ]);
        return response()->json($pastWinner);
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PastWinner  $pastWinner
     * @return \Illuminate\Http\Response
     */
    public function show(PastWinner $pastWinner)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PastWinner  $pastWinner
     * @return \Illuminate\Http\Response
     */
    public function edit(PastWinner $pastWinner)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PastWinner  $pastWinner
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PastWinner $pastWinner)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PastWinner  $pastWinner
     * @return \Illuminate\Http\Response
     */
    public function destroy($pastWinner)
    {
        $pastWinner = PastWinner::find($pastWinner);
        $pastWinner->delete();
        return response()->json(['message'=>'Record deleted successfully']);
    }
   
}
