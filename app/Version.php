<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class Version extends Model
{
	protected $table = "tblversion";
	public $timestamps = false;
	
	protected $fillable = ['app_version'];
    //
}
