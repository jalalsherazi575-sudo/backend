<?php

namespace Laraspace;

//use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Foundation\Auth\BusinessUsers as Authenticatable;

use Illuminate\Notifications\Notifiable;
use Auth;


class BusinessUsers extends Authenticatable
{
	use Notifiable;
	
	protected $table = "tblbusinessusers";
	public $timestamps = false;
	protected $guard = 'businessuser';
	protected $guarded = ['id'];
	protected $hidden = [
     'password', 'remember_token',
    ];

	protected $fillable = ['companyName','emailAddress','contactPersonName','profilePicture','countryId'];
    //

    public static function login($request)
    {
        $remember = 1;
        $email = $request->email;
        $password = $request->password;
        return (Auth::guard('businessuser')->attempt(['emailAddress' => $email, 'password' => $password], $remember));
    }
}
