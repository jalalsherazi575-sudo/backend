<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class CustomerLocation extends Model
{
	protected $table = "tblcustomerlocation";
	public $timestamps = false;
	//protected $guard = 'tblvender';
	protected $fillable = ['customerId'];
    //
}
