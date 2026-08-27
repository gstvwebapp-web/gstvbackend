<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Votegame extends Model
{
    use HasFactory;
    public $table = "votegames";
	protected $fillable = ['userID', 'question','answerOption','gameDetail','whowin','voteforwin','totalvote','status'];
}
