<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class IdProofType extends Model
{
	protected $table = "tblidprooftype";
	public $timestamps = false;
	
	protected $fillable = ['name'];
    //
}
