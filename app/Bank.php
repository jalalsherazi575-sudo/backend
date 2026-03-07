<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
	protected $table = "tblbanks";
	public $timestamps = false;
	
	protected $fillable = ['name'];
    //
}
