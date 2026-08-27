<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Epapercategory extends Model
{
    use HasFactory;
    public $table = "epapercategory";
	protected $fillable = ['userID','cattype', 'engtitle','title','catOrder','slug','featureImage','metatitle','metadesc','metakeyword','status'];
}