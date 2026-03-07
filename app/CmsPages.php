<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class CmsPages extends Model
{
	protected $table = "tblcms";
	public $timestamps = false;
	
	protected $fillable = ['name'];
    //
}
