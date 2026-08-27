<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;
    public $table = "modules";
	protected $fillable = ['title', 'moduleLink', 'parent_id', 'status'];
	
	/**
	 * Get the parent module
	 */
	public function parent()
	{
	    return $this->belongsTo(Module::class, 'parent_id');
	}
	
	/**
	 * Get the child modules (submenus)
	 */
	public function children()
	{
	    return $this->hasMany(Module::class, 'parent_id')->where('status', 'Active')->orderBy('id', 'asc');
	}
	
	/**
	 * Get all submenus for this module
	 */
	public function submenus()
	{
	    return $this->children();
	}
}
