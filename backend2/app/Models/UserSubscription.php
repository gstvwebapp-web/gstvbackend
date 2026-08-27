<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'plan',
        'razorpay_plan_id',
        'razorpay_subscription_id',
        'razorpay_payment_id',
        'amount',
        'status',
        'activated_at',
        'expires_at',
        'raw'
    ];

    protected $casts = [
        'raw' => 'array',
        'activated_at' => 'datetime',
        'expires_at' => 'datetime',
    ];
}
