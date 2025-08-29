<?php

namespace App\Models;
use Laravel\Passport\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Session;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password','mobile_no','address','type','company_id','status','ip_address',
    ];

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

    public function company(){
        return $this->hasMany('App\Models\Companies','user_id','id');
    }
    public function hasPrivilege($module_id, $action)
    {
        $priv = \App\Models\PrivilegesModuleMapping::where('employee_id', $this->id)
            ->where('module_id', $module_id)
            ->where('company_id', Session()->get('user_company_id'))
            ->first();

        return $priv;
    }
    public function hasModulePermission($module_id, $action)
    {
        $user = \App\Models\Companies::where('id', Session()->get('user_company_id'))->first();
        $priv = \App\Models\MerchantModuleMapping::where('module_id', $module_id)
            ->where('merchant_id', $user->user_id)
            ->first();

        return $priv;
    }
}
