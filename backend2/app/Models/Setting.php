<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;
    public $table = "settings";
	protected $fillable = ['id', 'userID', 'siteTitle', 'email', 'logo', 'headerScript', 'bodyScript', 'footerScript', 'newsdefaulttxt', 'newsdefaultapptxt', 'pointtitle', 'pointtxt', 'pointinfo', 'spendtime', 'point', 'pricemonth', 'pricemonthDiscount', 'priceyear', 'priceyearDiscount', 'metatitle', 'metakeyword', 'metadesc', 'livetvnews', 'epaper_url', 'rightbanner', 'middlebanner', 'rightbanner_status', 'middlebanner_status', 'rightbanner_link', 'middlebanner_link', 'announcement_link', 'rightbanner_ios_link', 'middlebanner_ios_link', 'announcement_ios_link', 'matterbanner', 'matterbanner_status', 'matterbanner_link', 'matterbanner_ios_link', 'created_at', 'announcement', 'announcement_status', 'fullcontent_status','election_status','election_link','election_height', 'updated_at','livescore','newfeatures','sensex','nifty','stockmarket','newfeatures_status','sudoku_link','home_page_video_big','trending','whatsupchannellink'];
}
