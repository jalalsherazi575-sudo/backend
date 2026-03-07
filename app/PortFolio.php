<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class PortFolio extends Model
{
	protected $table = "tblportfolio";
	public $timestamps = false;
	
	protected $fillable = ['name'];
    //
}
