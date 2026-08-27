<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDuration extends Model
{
    use HasFactory;
    public $table = "user_durations";
	protected $fillable = ['id', 'user_id','date','total_duraion','created_at','updated_at'];

}