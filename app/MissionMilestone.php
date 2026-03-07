<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class MissionMilestone extends Model
{
	protected $table = "tblmissionmilestone";
	public $timestamps = false;
	
	protected $fillable = ['title'];
    //

   
}
