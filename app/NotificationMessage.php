<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class NotificationMessage extends Model
{
	protected $table = "tblnotificationmessage";
	public $timestamps = false;
	
	protected $fillable = ['title_key'];
    //
}
