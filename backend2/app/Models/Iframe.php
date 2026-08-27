<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Iframe extends Model
{
    use HasFactory;
    public $table = "iframe";
	protected $fillable = ['userID', 'title','url','status'];
}
