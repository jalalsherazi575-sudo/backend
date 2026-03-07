<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
	protected $table = "tblcountries";
	public $timestamps = false;
	
	protected $fillable = ['name'];
    //
}
