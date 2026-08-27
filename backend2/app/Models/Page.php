<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;
    public $table = "pages";
	protected $fillable = ['userID', 'parentID','title','slug','featureImage','description','metatitle','metadesc'];
}
