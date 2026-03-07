<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class VendorBusiness extends Model
{
	protected $table = "tblvenderbusiness";
	public $timestamps = false;
	
	protected $fillable = ['venderId','firmName','incorporationDate','location','latitude'];
    //
}
