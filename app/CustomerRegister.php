<?php

namespace Laraspace;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class CustomerRegister extends Authenticatable
{
	use HasApiTokens, HasFactory, Notifiable;
	
    protected $table = "tblcustomerregister";
	public $timestamps = false;

	/**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password','gender','remember_token'
    ];

	protected $fillable = [
		'name',
		'phone',
		'password',
		'birthDate',
		'gender',
		'email',
		'photo',
		'countryId',
		'isActive',
		'OTP',
		'socialId',
		'remember_token',
		'isVerified',
		'verifiedDate',
		'createdDate',
		'updatedDate',
		'deviceType',
		'deviceToken',
		'deviceDetails',
		'loginType',
		'loginStatus',
		'lastLoginDate',
		'isTerms',
		'isMarketingConsent',
		'phoneVerificationSentRequestTime'
	];

	protected $appends = ['photo_image_url'];

    public function getPhotoImageUrlAttribute()
    {
        if($this->photo == ""){
            return "";
        }else{
        	return url('/customerregisterphoto/thumbnail_images/'.$this->photo);
            /*if(empty($this->socialId)){
        		return url('/customerregisterphoto/thumbnail_images/'.$this->photo);
            } else {
            	return $this->photo;
            }*/
        }
    }

	public function countryDetail()
	{
		return $this->belongsTo('Laraspace\Country', 'countryId','id')->select('id','currency','name','sortname','callingCode','flag');
	}

	public function comments()
    {
        return $this->hasMany(QuestionCommnent::class, 'userId', 'id');
    }

     public function usersubscriptionplan()
	{
	    return $this->hasMany(UserSubscriptionPlan::class, 'userId', 'id');
	}
	 public function transactionDetails()
    {
        return $this->hasMany(TransactionDetails::class, 'customer_id');
    }
}
