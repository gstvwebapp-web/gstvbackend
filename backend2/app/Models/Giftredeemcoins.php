<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Giftredeemcoins extends Model
{
    use HasFactory;
    public $table = "giftredeemcoins";
	protected $fillable = ['id', 'user_id','giftid','spentcoins','address','mobilenumber','status','created_at','updated_at'];
}