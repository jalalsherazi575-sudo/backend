<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class CustomerRegister extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
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
        'phoneVerificationSentRequestTime'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    public function countryDetail()
    {
        return $this->belongsTo('Laraspace\Country', 'countryId','id')->select('id','currency','name','sortname','callingCode','flag');
    }
}
