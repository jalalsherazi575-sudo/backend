<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class ConsumerManagementLogo extends Model
{
	protected $table = "tblconsumermanagementlogo";
	public $timestamps = false;
	//protected $guard = 'tblvender';
	protected $fillable = ['consumerAffairsLogo','ministryofHealthLogo','isActive'];
    //
}
