<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class DeviceToken extends Model
{
    protected $table = "tbldevicetoken";
	public $timestamps = false;
	protected $fillable = ['customerId','deviceType','deviceToken','loginStatus','deviceDetails','tokenDate','isCustomer','logoutDateTime'];
}
