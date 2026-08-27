<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    use HasFactory;
    public $table = "votes";
	protected $fillable = ['userID', 'pollID','selectedAnswer','IPaddress','coin'];
}
