<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campuscorner extends Model
{
    use HasFactory;
    public $table = "campuscorner";
	protected $fillable = ['userID','id', 'name','title','school','description','video_status','featureImage','video','city','adminid','agree_status','status','created_at','updated_at','drop_reason'];
}