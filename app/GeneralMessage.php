<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class GeneralMessage extends Model
{
	protected $table = "tblgeneralmessage";
	public $timestamps = false;
	
	protected $fillable = ['name'];
    //
}
