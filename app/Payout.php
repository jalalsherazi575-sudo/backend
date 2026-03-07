<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class Payout extends Model
{
	protected $table = "tblusercredithistory";
	public $timestamps = false;
	
	protected $fillable = ['type'];
    //
}
