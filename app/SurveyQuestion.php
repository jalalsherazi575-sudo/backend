<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class SurveyQuestion extends Model
{
	protected $table = "tblsurveyquestion";
	public $timestamps = false;
	//protected $guard = 'tblvender';
	protected $fillable = ['question'];
    //
}
