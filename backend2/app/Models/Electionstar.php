<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Electionstar extends Model
{
    use HasFactory;
    public $table = "electionstar";
	protected $fillable = ['id', 'stateId','title','city','party_status','partytype','image','status','created_at','updated_at'];
}