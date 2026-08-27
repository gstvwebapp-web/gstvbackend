<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Astrology extends Model
{
    use HasFactory;
    public $table = "astrologies";
	protected $fillable = ['fullname', 'email','phone','birth_date','birth_time','birth_place'];
}