<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class BusinessUsersBeacon extends Model
{
	protected $table = "tblbusinessusersbeacon";
	public $timestamps = false;
	protected $guard = 'tblbusinessusersbeacon';
	protected $fillable = ['businessId','beaconName','nameSpaceId','instanceId','building'];
    //
}
