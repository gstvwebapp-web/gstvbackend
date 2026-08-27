<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    public $table = "categories";
    protected $fillable = ['userID', 'category_name_guj','parentID','title','catOrder','eventCat','slug','featureImage','icon','description','metatitle','metadesc','settopmenucat','settopmenucat_ord','settopvideos','topcatOrd'];

    // Category.php (Model)
    public function children()
    {
        return $this->hasMany(Category::class, 'parentID', 'id');
    }

}