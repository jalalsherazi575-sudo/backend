<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class LevelManagement extends Model
{
	protected $table = "tbllevelmanagement";
	public $timestamps = false;
	protected $primaryKey = 'levelId';
	
	protected $fillable = ['name'];
     protected $appends = ['cat_image_url'];

    public function getCatImageUrlAttribute()
    {
        if($this->catImage == ""){
            return "";
        }else{
            return url('/images/category/'.$this->catImage);
        }
    }
      /**
     * Define the relationship with the transactionDetails model.
     */
     public function transactionDetails()
    {
        return $this->hasMany('Laraspace\TransactionDetails', 'category_id');
    }

}
