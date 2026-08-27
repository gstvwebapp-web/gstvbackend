<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;
    public $table = "plan";
	protected $fillable = ['id','plan_name', 'plan_title','plan_subscription_id','plan_amount','plan_discount','plan_discount_amount','status'];
}