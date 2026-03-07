<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
	protected $table = "tblsubscriptionplans";
	public $timestamps = false;
	
	protected $fillable = ['name'];
    //
}
