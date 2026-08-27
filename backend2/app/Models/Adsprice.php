<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Adsprice extends Model
{
    use HasFactory;
    public $table = "adsprice";
	protected $fillable = ['userID','ads_position', 'price','status', 'created_at', 'updated_at'];
}