<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PollWinner extends Model
{
    use HasFactory;

    protected $table = 'poll_winners'; // table name

    protected $fillable = [
        'pollID',
        'userID',
        'voteID',
        'authorName',
        'mobile',
        'selectedAnswer',
    ];

    // Relationships (if you want)
    public function poll()
    {
        return $this->belongsTo(Poll::class, 'pollID');
    }

    public function user()
    {
        return $this->belongsTo(UserRegister::class, 'userID');
    }

    public function vote()
    {
        return $this->belongsTo(Vote::class, 'voteID');
    }
}
