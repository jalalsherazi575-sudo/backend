<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class AppNoticeBoard extends Model
{
	protected $table = "tblappnoticeboard";
	public $timestamps = false;
	//protected $primaryKey = 'levelId';
	
	protected $fillable = ['description','photo','sortOrder','isActive'];
    //
}
