<?php

namespace Laraspace;

use Illuminate\Database\Eloquent\Model;

class UsersRoles extends Model
{
    protected $table = "user_roles";
    public $timestamps = false;
    protected $primaryKey = 'role_id';
    protected $fillable = ['role_name'];
    //
}
