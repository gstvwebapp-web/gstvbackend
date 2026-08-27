<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gift extends Model
{
    use HasFactory;
    public $table = "gift";
	protected $fillable = ['id', 'giftimage','gifttitle','coins','qty','status','created_at','updated_at'];
}