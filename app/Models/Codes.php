<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Codes extends Model
{
    use HasFactory;
    protected $fillable =['code','area','status','inMessageId','prizeWon'];

    // protected $with = ['message'];

    // public function message(){
    //     return $this->belongsTo(InMessages::class,'inMessageId');
    // }
}
