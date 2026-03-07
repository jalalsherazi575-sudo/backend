<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class GeneralNotification extends Model
{
	protected $table = "tblgeneralmessage";
	public $timestamps = false;
	
	protected $fillable = ['notification'];
    //
}
