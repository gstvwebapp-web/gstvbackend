<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Userganapati extends Model
{
    use HasFactory;
    public $table = "user_ganapati";
	protected $fillable = ['userID','id', 'name','address','featureImage','status','created_at','updated_at'];
}