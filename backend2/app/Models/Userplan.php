<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Userplan extends Model
{
    use  HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    public $table = "user_plan";
    protected $fillable = [
        'id',
        'user_id',
        'plan','plan_amount','plan_expire','plan_status','plan_order_id','plan_payment_id'
    ];
}
