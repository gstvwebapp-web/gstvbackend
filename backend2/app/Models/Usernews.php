<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Usernews extends Model
{
    use HasFactory;
    public $table = "usernews";
	protected $fillable = ['userID', 'title','featureImage','description','created_at','updated_at','status'];
}
