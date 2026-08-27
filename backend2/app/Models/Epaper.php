<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Epaper extends Model
{
    use HasFactory;
    public $table = "epaper";
	protected $fillable = ['userID','ecatID', 'etitle','engtitle','slug','newspaperdate','pdf','thumbnail','Story_data','metatitle','metadesc','metakeyword','status'];
}