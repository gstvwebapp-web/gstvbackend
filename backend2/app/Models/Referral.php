<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    use HasFactory;
    public $table = "referral";
	protected $fillable = ['id', 'referaltitle','referralcode','points','device_id','created_at';
 

}