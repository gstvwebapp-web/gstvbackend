<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Newsarchive extends Model
{
    use HasFactory;
    public $table = "news_archive";
	protected $fillable = ['userID', 'catID','title','slug','englishTitle','featureImage','newsOrder','topnewsOrder','uploadVideo','videoURL','scheduleDate','is_home','viewer','tags','imageURL','metakeyword','related_news','assignUserID','description','metatitle','metadesc','status','created_at'];
}
