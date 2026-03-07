<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class PortFolioFiles extends Model
{
	protected $table = "tblportfoliofiles";
	public $timestamps = false;
	
	protected $fillable = ['file'];
    //
}
