<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Validation\Rules\Password;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The password complexity bar for every place in the app that sets an
     * account's password (self-service change, admin/manager reset, forgot-
     * password, and manual account creation) — one shared definition so
     * they can't drift apart. Combine with 'required', 'string', and
     * 'confirmed' (where that field exists) at each call site.
     */
    public static function passwordComplexityRule(): Password
    {
        return Password::min(8)->mixedCase()->numbers()->symbols();
    }

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
     * Get the staff profile linked to this account.
     */
    public function staff()
    {
        return $this->hasOne(Staff::class);
    }

    /**
     * The dashboard path for this account's role. Single source of truth for
     * both the post-login redirect and the redirect an already-authenticated
     * user gets when visiting a guest-only page like /login.
     */
    public function dashboardUrl(): string
    {
        return match ((int) $this->roleID) {
            1 => '/admin/dashboard',
            2 => '/manager/dashboard',
            3 => '/farmer/dashboard',
            default => '/',
        };
    }

    /**
     * Which role layout a shared page (My Profile, Settings) should render
     * inside, so one view file works for all three roles instead of three
     * near-identical copies.
     */
    public function layoutView(): string
    {
        return match ((int) $this->roleID) {
            1 => 'admin.layout',
            2 => 'manager.layout',
            default => 'farmer.layout',
        };
    }

    /**
     * Get the profile picture URL, sourced from Staff (Admin/Manager) or
     * Farmer (Farmer), whichever this account is linked to.
     */
    public function getProfilePictureUrlAttribute(): ?string
    {
        $path = in_array((int) $this->roleID, [1, 2], true)
            ? $this->staff?->profile_picture
            : $this->farmer?->profile_picture;

        return $path ? asset('storage/'.$path) : null;
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
        'temp_password',
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
        'temp_password',
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
            'LockedUntil' => 'datetime',
        ];
    }
}
