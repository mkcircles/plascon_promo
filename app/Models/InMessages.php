<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InMessages extends Model
{
    use HasFactory;

    protected $fillable =['msisdn','inText','response','status'];
}
