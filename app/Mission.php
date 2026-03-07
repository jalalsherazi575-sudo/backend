<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class Mission extends Model
{
	protected $table = "tblmission";
	public $timestamps = false;
	
	protected $fillable = ['missionName'];
    //

    public function businessusers()
    {
        return $this->hasOne('App\BusinessUsers');
    }
}
