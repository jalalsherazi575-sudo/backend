<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class VendorProof extends Model
{
	protected $table = "tblvenderproof";
	public $timestamps = false;
	
	protected $fillable = ['venderId','photo'];
    //
}
