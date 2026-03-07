<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class planSubject extends Model
{
	protected $table = "plan_subject";
	  // Define relationships if needed
    protected $fillable = ['plan_id','subject_id','categoryId'];

    public function planpackage()
    {
        return $this->belongsTo(PlanPackage::class, 'plan_id');
    }

    public function subjects()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
}
