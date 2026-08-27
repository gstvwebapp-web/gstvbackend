<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Newslike extends Model
{
    use  HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    public $table = "newslike";
    protected $fillable = [
        'user_id',
        'ipAddress',
        'news_id',
        'status',
    ];
}
