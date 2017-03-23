<?php

namespace LaravelEnso\Core\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Enso\Notifications\ResetPasswordNotification;
use Laravel\Passport\HasApiTokens;
use LaravelEnso\CnpValidator\Validations;
use LaravelEnso\Core\Enums\IsActiveEnum;
use LaravelEnso\Core\Http\Controllers\Core\PreferencesController;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens;

    protected $fillable = [
        'first_name', 'last_name', 'phone', 'nin', 'is_active', 'role_id',
    ];

    protected $hidden = [
        'password', 'remember_token', 'api_token',
    ];

    protected $appends = ['avatar_link', 'full_name'];

    public function owner()
    {
        return $this->belongsTo('LaravelEnso\Core\Models\Owner');
    }

    public function avatar()
    {
        return $this->hasOne('LaravelEnso\Core\Models\Avatar');
    }

    public function role()
    {
        return $this->belongsTo('LaravelEnso\Core\Models\Role');
    }

    public function logins()
    {
        return $this->hasMany('LaravelEnso\Core\Models\Login');
    }

    public function preferences()
    {
        return $this->hasMany('LaravelEnso\Core\Models\Preference');
    }

    public function comments()
    {
        return $this->hasMany('LaravelEnso\CommentsManager\Comment');
    }

    public function comments_tags()
    {
        return $this->belongsToMany('App\Comment');
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

    public function actionsHistories()
    {
        return $this->hasMany('LaravelEnso\ActionLogger\ActionsHistory');
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
        return \Date::parse($this->created_at)->format('d-m-Y');
    }

    public function getBirthdayAttribute()
    {
        $birthday = 'N/A';

        if ($this->nin && Validations::validatorNin('', $this->nin)) {
            $type = substr($this->nin, 0, 1);
            $year = substr($this->nin, 1, 2);
            $month = substr($this->nin, 3, 2);
            $day = substr($this->nin, 5, 2);

            if ($type == '5' || $type == '6') {
                $year = '20'.$year;
            } else {
                $year = '19'.$year;
            }

            $birthday = \Date::parse($year.$month.$day)->format('d-m-Y');

            if ($birthday == \Date::now()->format('d-m-Y')) {
                $birthday = __('Happy Birthday');
            }
        }

        return $birthday;
    }

    public function getActiveLabelAttribute()
    {
        $isActiveEnum = new IsActiveEnum();

        return $isActiveEnum->getValueByKey($this->is_active);
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($this, $token));
    }
}
