<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rashifaldata extends Model
{
    use HasFactory;
    public $table = "rashifaldata";
	protected $fillable = ['id', 'user_id','rashifal_id','authorname','authoricon','slug','rashidescription','created_at','updated_at'];
}