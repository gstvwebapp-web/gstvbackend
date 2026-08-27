<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Usermygujarat extends Model
{
    use HasFactory;
    public $table = "user_mygujarat";
	protected $fillable = ['userID','id', 'name','title','description','video_status','featureImage','video','city','adminid','agree_status','status','created_at','updated_at','drop_reason'];
}