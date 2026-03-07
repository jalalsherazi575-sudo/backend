<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class HowDidYouKnow extends Model
{
	protected $table = "tblhowdidyouknow";
	public $timestamps = false;
	
	protected $fillable = ['name'];
    //
}
