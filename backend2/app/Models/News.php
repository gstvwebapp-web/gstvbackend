<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;
    public $table = "news";
	protected $fillable = ['userID', 'catID','title','slug','englishTitle','relatednewsid','featureImage','newsOrder','is_valid','hide_title','video_webp','topnewsOrder','uploadVideo','videoURL','scheduleDate','is_home','viewer','tags','imageURL','metakeyword','related_news','assignUserID','description','metatitle','metadesc','status','usernews_userID','is_live_news','is_breaking','is_vertical_video','viewer_app','notification','inshorts','img_credit_txt','premium_news','audio_path','video_start','newssource','is_video_horizontal','is_jacket','jacket_title','voiceovertext'];
}
