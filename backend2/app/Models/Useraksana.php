<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Useraksana extends Model
{
    use HasFactory;
    public $table = "user_aksana";
	protected $fillable = ['userID','id', 'name','days','mobile','address','featureImage','video','status','created_at','updated_at'];
}