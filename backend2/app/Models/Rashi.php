<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rashi extends Model
{
    use HasFactory;
    public $table = "rashifal";
	protected $fillable = ['id', 'user_id','title','engtitle','slug','rashiword','rashiicon','status','created_at','updated_at'];
}