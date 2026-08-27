<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Webstory extends Model
{
    use HasFactory;
    public $table = "webstory";
	protected $fillable = ['userID', 'catID','title','englishTitle','slug','Story_data','viewer','status','metakeyword','metatitle','metadesc'];

    protected $casts = [
        'Story_data' => 'json',
    ];

}