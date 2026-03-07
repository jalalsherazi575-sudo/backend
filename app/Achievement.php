<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
	protected $table = "tblachievement";
	public $timestamps = false;
	
	protected $fillable = ['name'];
    //
}
