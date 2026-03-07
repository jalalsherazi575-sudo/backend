<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class AdsSubscriptionPlan extends Model
{
	protected $table = "tbladssubscriptionplan";
	public $timestamps = false;
	
	protected $fillable = ['planName'];
    //
}
