<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class DefaultConfiguration extends Model
{
	protected $table = "default_configuration";
	public $timestamps = false;
	protected $primaryKey = 'id';

	//protected $fillable = ['templateName'];
    //
}
