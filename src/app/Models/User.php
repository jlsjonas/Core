<?php

namespace LaravelEnso\Core\app\Models;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use LaravelEnso\Core\app\Http\Controllers\Core\PreferencesController;
use LaravelEnso\Core\app\Notifications\ResetPasswordNotification;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'first_name', 'last_name', 'phone', 'is_active', 'role_id',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $appends = ['avatar_link', 'full_name'];

    public function owner()
    {
        return $this->belongsTo('LaravelEnso\Core\app\Models\Owner');
    }

    public function avatar()
    {
        return $this->hasOne('LaravelEnso\Core\app\Models\Avatar');
    }

    public function role()
    {
        return $this->belongsTo('LaravelEnso\Core\app\Models\Role');
    }

    public function logins()
    {
        return $this->hasMany('LaravelEnso\Core\app\Models\Login');
    }

    public function preferences()
    {
        return $this->hasMany('LaravelEnso\Core\app\Models\Preference');
    }

    public function getAvatarLinkAttribute()
    {
        return $this->avatar ? '/core/avatars/'.$this->avatar->saved_name : asset('/images/profile.png');
    }

    public function getLanguageAttribute()
    {
        return json_decode($this->global_preferences)->lang;
    }

    public function getGlobalPreferencesAttribute()
    {
        return PreferencesController::getPreferences('global');
    }

    public function getPreferences($page)
    {
        return PreferencesController::getPreferences($page);
    }

    public function action_histories()
    {
        return $this->hasMany('LaravelEnso\ActionLogger\app\Models\ActionHistory');
    }

    public function isAdmin()
    {
        return $this->role_id == 1;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function hasAccessTo($route)
    {
        return $this->role->permissions->pluck('name')->search($route) !== false;
    }

    public function setImpersonating($id)
    {
        session()->put('impersonate', $id);
    }

    public function stopImpersonating()
    {
        session()->forget('impersonate');
    }

    public function isImpersonating()
    {
        return session()->has('impersonate');
    }

    public function getFullNameAttribute()
    {
        return $this->first_name.' '.$this->last_name;
    }

    public function getCreatedDateAttribute()
    {
        return Carbon::parse($this->created_at)->format('d-m-Y');
    }

    public function getBirthdayAttribute()
    {
        //
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($this, $token));
    }
}
