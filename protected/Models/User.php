<?php

namespace App\Models;

use T4\Orm\Model;

/**
 * Class User
 * @package App\Models
 * @property string $email
 * @property string $password
 * @property \T4\Core\Collection|\App\Models\Role[] $roles
 */
class User
    extends Model
{
    protected static $schema = [
        'table' => '__users',
        'columns' => [
            'email'     => ['type'=>'string'],
            'password'  => ['type'=>'string'],
        ],
        'relations' => [
            'role' => ['type' => self::MANY_TO_MANY, 'model' => \App\Models\Role::class],
        ]
    ];

    public function getRoleNames()
    {
        return $this->roles->collect('name');
    }



    public function hasRole($role)
    {
        return !empty($this->role) && ( ($role == $this->role[0]->name) || ($role == $this->role[0]->title) );
    }

}