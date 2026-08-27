<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitorSessions extends Model
{
    use HasFactory;
    public $table = "visitor_sessions";
	protected $fillable = ['id', 'user_id','session_id','start_time','end_time','duration','created_at','updated_at'];

}