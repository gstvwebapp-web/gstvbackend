<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Adsimpression extends Model
{
    use HasFactory;
    public $table = "adsimpression";
	protected $fillable = ['id','title', 'image','ip','view','click','pageurl'];
}