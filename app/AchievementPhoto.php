<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class AchievementPhoto extends Model
{
	protected $table = "tblachievementphoto";
	public $timestamps = false;
	
	protected $fillable = ['photo'];
    //
}
