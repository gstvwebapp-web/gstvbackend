<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Podcast extends Model
{
    use HasFactory;
    public $table = "podcast";
	protected $fillable = ['userID','authorid', 'podcastTitle','podcastDescription', 'audioclip','audiocliptime', 'audioclipimg', 'viewer', 'viewer_app', 'status'];
}