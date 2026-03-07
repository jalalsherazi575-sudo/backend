<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class TransactionDetails extends Model
{
	protected $table = "transaction_details";
	public $timestamps = false;

	/**
     * Define the relationship with the Subject model.
     */
    public function subject()
    {
        return $this->belongsTo('Laraspace\Subject', 'subject_id');
    }

    /**
     * Define the relationship with the Category model.
     */
    public function category()
    {
        return $this->belongsTo('Laraspace\LevelManagement', 'category_id');
    }

    /**
     * Define the relationship with the Plan model.
     */
    public function planpackage()
    {
        return $this->belongsTo('Laraspace\PlanPackage', 'plan_id');
    }

    /**
     * Define the relationship with the Transaction model.
     */
    public function transaction()
    {
        return $this->belongsTo('Laraspace\TransactionMaster', 'transaction_id');
    }
    /**
     * Define the relationship with the Transaction model.
     */
    public function customer()
    {
        return $this->belongsTo('Laraspace\CustomerRegister', 'customer_id');
    }
	
}
