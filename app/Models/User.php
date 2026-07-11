<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the role associated with the user.
     */
    public function role()
    {
        return $this->belongsTo(\App\Models\Role::class, 'roleID');
    }

    /**
     * Get the role name attribute.
     */
    public function getRoleNameAttribute()
    {
        return $this->role ? $this->role->name : 'Farmer';
    }

    /**
     * Get the farmer membership record linked to this account.
     */
    public function farmer()
    {
        return $this->hasOne(Farmer::class, 'account_user_id');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'name',
        'email',
        'password',
        'status',
        'isloggedin',
        'roleID',
        'Phonenumber',
        'OTP',
        'OTPexpriry',
        'firstTimelogin',
        'FailedLoginAttemps',
        'LockedUntil',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
