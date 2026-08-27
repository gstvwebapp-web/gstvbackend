<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Livenews extends Model
{
    use HasFactory;
    public $table = "livenews";
	protected $fillable = ['lid','user_id', 'news_id','livetitle','livedesc','livenewsdate'];
}