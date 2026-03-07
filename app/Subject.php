<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;
use Laraspace\TransactionDetails;
use Laraspace\Topics;
class Subject extends Model
{
	protected $table = "subject";
	public $timestamps = false;
	
	public function isPurchased()
    {
        return $this->belongsTo(TransactionDetails::class, 'id','subject_id');
    }

    public function topics(){
    	return $this->belongsTo(Topics::class, 'id','subjectId');
    }

    public function category()
    {
        return $this->belongsTo(LevelManagement::class, 'categoryId','levelId');
    }
     public function planpackage()
    {
        return $this->belongsTo(PlanPackage::class, 'packageId');
    }
}
