<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Newscategory extends Model
{
    use HasFactory;
    public $table = "news_category";
	protected $fillable = ['news_id', 'category_id'];
    public $timestamps = true; // or false if your table doesn't have timestamps
}