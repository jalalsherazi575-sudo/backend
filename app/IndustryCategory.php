<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class IndustryCategory extends Model
{
	protected $table = "tblindustrycategory";
	public $timestamps = false;
	
	protected $fillable = ['name'];
    //
}
