<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class MissionQuestionaire extends Model
{
	protected $table = "tblmissionquestionnaire";
	public $timestamps = false;
	
	protected $fillable = ['title'];
    //

   
}
