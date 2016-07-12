<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */


    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function genToken(){
        $tokenText = microtime(1) . $this->password . $this->name;
        $api_token = bcrypt($tokenText);
        $this->api_token = $api_token;
        $this->save();
        return $api_token;
    }

}
