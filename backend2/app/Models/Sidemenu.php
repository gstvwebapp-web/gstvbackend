<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sidemenu extends Model
{
    use HasFactory;
    public $table = "sidemenu";
	protected $fillable = ['menu', 'menuicon','menuaction','menustatus','loginstatus','menuorder'];
}