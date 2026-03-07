<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class NotificationMessageTranslation extends Model
{
	protected $table = "tblnotificationmessagetranslation";
	public $timestamps = false;
	
	protected $fillable = ['title_value'];
    //
}
