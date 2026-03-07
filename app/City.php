<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
	protected $table = "tblcities";
	public $timestamps = false;
	
	protected $fillable = ['name'];
    //
}
