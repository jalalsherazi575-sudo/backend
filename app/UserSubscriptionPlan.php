<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class UserSubscriptionPlan extends Model
{
    protected $table = "tblusersubscription";
	public $timestamps = false;
	//protected $primaryKey = 'planId';
	protected $fillable = ['userId','packageId','packageName','createdDate','updatedDate','packagePrice','isActive','isCancel','cancelDate','packagePeriodInMonth','expiryDate','androidPlanKey','iosPlanKey','jsonresponseobject','fromDate','assignAdminId','isAssignedByAdmin','adminNote'];

	public function customerregister()
    {
        return $this->belongsTo(CustomerRegister::class, 'userId', 'id');
    }

}
