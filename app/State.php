<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
	protected $table = "tblstates";
	public $timestamps = false;
	
	protected $fillable = ['name'];
    //
}
