<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Graphic extends Model
{
    use HasFactory;
    public $table = "graphic";
	protected $fillable = ['title','sitelink','images','status'];

   
}