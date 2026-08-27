<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UpcomingCricket extends Model
{
    use HasFactory;
    public $table = "upcoming_cricket";
	protected $fillable = ['id','series', 'series_id','match_time','venue','match_type','date_wise','status'];
}