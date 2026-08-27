<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deletenews extends Model
{
    use HasFactory;
    
    protected $table = "deletenews";
    
    // Set the fillable attributes
    protected $fillable = ['start_date', 'end_date', 'type'];
}
