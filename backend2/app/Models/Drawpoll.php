<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Drawpoll extends Model
{
    use HasFactory;
    public $table = "drawpoll";
	protected $fillable = ['firstname','id', 'lastname','address','mobile','pollID','voteId','created_at','updated_at'];
}