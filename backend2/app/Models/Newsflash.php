<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Newsflash extends Model
{
    use HasFactory;
    public $table = "newsflash";
	protected $fillable = ['title', 'status', 'updated_at', 'created_at'];
}