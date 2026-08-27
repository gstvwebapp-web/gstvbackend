<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Electionresults extends Model
{
    use HasFactory;
    public $table = "electionresults";
	protected $fillable = ['userID','stateId','party','electiontitle','totalseats','status','years'];
}
