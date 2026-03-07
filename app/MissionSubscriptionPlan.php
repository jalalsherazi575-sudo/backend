<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class MissionSubscriptionPlan extends Model
{
	protected $table = "tblmissionsubscriptionplan";
	public $timestamps = false;
	
	protected $fillable = ['planName'];
    //
}
