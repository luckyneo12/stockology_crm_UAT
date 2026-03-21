<?php

namespace App\Models;

use Laratrust\Models\Role as RoleModel;

class Role extends RoleModel
{
    public $guarded = [];

    protected $fillable = [
        'name',
        'guard_name',
        'module',
        'created_by',
        'allowed_login_ips',
    ];


    public function users()
    {
        return $this->belongsToMany(User::class, 'role_user', 'role_id', 'user_id');
    }
}
