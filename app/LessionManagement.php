<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class LessionManagement extends Model
{
	protected $table = "tbllessionmanagement";
	public $timestamps = false;
	protected $primaryKey = 'lessionId';
	
	protected $fillable = ['lessionName'];
    //
}
