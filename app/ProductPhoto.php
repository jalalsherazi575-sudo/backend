<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class ProductPhoto extends Model
{
	protected $table = "tblcustomerproductphoto";
	public $timestamps = false;
	
	protected $fillable = ['photo'];
    //
}
