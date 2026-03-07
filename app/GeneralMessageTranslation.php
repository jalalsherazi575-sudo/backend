<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class GeneralMessageTranslation extends Model
{
	protected $table = "tblgeneralmessagetranslation";
	public $timestamps = false;
	
	protected $fillable = ['name'];
    //
}
