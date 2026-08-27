<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reviews extends Model
{
    use HasFactory;
    public $table = "reviews";
    protected $primaryKey = 'id'; // your primary key
    public $incrementing = true;
	protected $fillable = ['id','name','rating','phone','city', 'magazine','description','created_at','updated_at'];
}