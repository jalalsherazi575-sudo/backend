<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class ConsumerManager extends Model
{
	protected $table = "tblconsumermanager";
	public $timestamps = false;
	//protected $guard = 'tblvender';
	protected $fillable = ['name','emailId','password'];
    //
}
