<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Career extends Model
{
    use HasFactory;
    public $table = "career";
    protected $primaryKey = 'id'; // your primary key
    public $incrementing = true;
	protected $fillable = ['id','fullname','email','phone','post', 'experience','message','cv','created_at'];
}