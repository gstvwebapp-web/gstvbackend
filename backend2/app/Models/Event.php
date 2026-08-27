<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;
    public $table = "event";
    protected $primaryKey = 'event_id'; // your primary key
    public $incrementing = true;
	protected $fillable = ['event_id','userID','event_title','event_eng_title','event_slug', 'event_city','event_location','event_desc','event_start_date','event_end_date','event_img','viewer','viewer_app','status','created_at','updated_at'];
}