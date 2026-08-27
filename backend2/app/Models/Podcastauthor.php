<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Podcastauthor extends Model
{
    use HasFactory;
    public $table = "podcast_author";
	protected $fillable = ['userID','authorname', 'authorengname', 'authorslug', 'authorimg', 'viewer', 'status'];
}