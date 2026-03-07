<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
	protected $table = "tblproductcategory";
	public $timestamps = false;
	
	protected $fillable = ['name'];
    //
}
