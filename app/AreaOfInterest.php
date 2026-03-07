<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class AreaOfInterest extends Model
{
	protected $table = "tblareaofinterest";
	public $timestamps = false;
	
	protected $fillable = ['name'];
    //
}
