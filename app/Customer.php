<?php

namespace Laraspace;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Customer extends Authenticatable
{
	use HasApiTokens, HasFactory, Notifiable;

	protected $table = "tblcustomer";
	public $timestamps = false;
	protected $guard = 'tblvender';
	protected $fillable = ['fname','email','password','phone'];
    //
}
