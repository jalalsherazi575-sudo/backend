<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
	protected $table = "email_template";
	public $timestamps = false;
	protected $primaryKey = 'id';

	//protected $fillable = ['templateName'];
    //
}
