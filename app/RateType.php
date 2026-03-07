<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class RateType extends Model
{
	protected $table = "tblratetype";
	public $timestamps = false;
	
	protected $fillable = ['name'];
    //
}
