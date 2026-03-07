<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class PasswordResets extends Model
{
	protected $table = "password_resets";
	 protected $fillable = [
        'email',
        'token',
        'expires_at',
    ];
}
