<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class PlanPackage extends Model
{
    protected $table = "tblplanpackage";
	public $timestamps = false;
	protected $primaryKey = 'packageId';
	protected $fillable = ['packageName','packagePrice','packageDescription','isActive','createdDate','updatedDate','packagePeriodInMonth','androidPlanKey','iosPlanKey','packageImages','subjectId'];

	 public function subjects()
    {
         return $this->belongsTo(Subject::class, 'subjectId');
    }
}
