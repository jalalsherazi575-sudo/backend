<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class ConsumerForum extends Model
{
	protected $table = "tblcustomerconsumerforum";
	public $timestamps = false;
	//protected $guard = 'tblvender';
	protected $fillable = ['consumerForumType'];
    //
}
