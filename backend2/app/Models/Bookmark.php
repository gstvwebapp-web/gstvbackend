<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bookmark extends Model
{
    use  HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    public $table = "bookmark";
    protected $fillable = [
        'user_id',
        'news_id',
        'bookmark_type',
        'status',
        'created_at',
    ];
}
