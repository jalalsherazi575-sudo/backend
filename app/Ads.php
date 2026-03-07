<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class Ads extends Model
{
	protected $table = "tblads";
	public $timestamps = false;
	
	protected $fillable = ['adsName'];
    //

    
}
