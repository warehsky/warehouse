<?php

namespace App\Model;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;

class Admin extends Authenticatable
{
    const ROLE_USER = 1;
    const ROLE_GUEST = 0;
    const ROLE_ADMIN = 10;
    use Notifiable;
    use HasRoles;
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'login', 'role', 'note','chatName'
    ];
    protected $connection = 'mysql';
    protected $dates = ['deleted_at'];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    protected $guard_name = 'web';
    /**
     * Проверка роли админа
     */
    public function isAdmin(): bool
    {
        return (int)$this->role === (int)self::ROLE_ADMIN;
    }
    public function role()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }
    public function generateToken()
    {
        $this->api_token = \Str::random(60);
        
        $this->save();

        return $this->api_token;
    }
    /** */
    public function getToken(){
        return $this->api_token;
    }
}
