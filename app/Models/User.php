<?php

namespace App\Models;
use Laravel\Passport\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Session;
use DB;

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
        
            if (Session()->get('user_type')=="OWNER" && Session::get('admin_id')!="") {
                $priv = DB::table('admin_user_privileges_module_mappings')->where('user_id', Session::get('admin_id'))
                    ->where('module_id', $module_id)
                    ->first();
                    
            }else{
                $comp = \App\Models\Companies::where('id', Session()->get('user_company_id'))
                                                ->first();
                $comp_ids = \App\Models\Companies::where('user_id', $comp->user_id)
                                                ->pluck('id');
                
                $user_data = User::whereIn('company_id',$comp_ids)
                                ->where('mobile_no',$this->mobile_no)
                                ->first();
                
                        Session::put([
                            'user_id' => $user_data->id,
                            'user_name' => $user_data->name,
                            'user_email' => $user_data->email,
                            'user_mobile_no' => $user_data->mobile_no,
                        ]);
                // print_r($u);
                // die($module_id);
                $priv = \App\Models\PrivilegesModuleMapping::where('employee_id', $user_data->id)
                    ->where('module_id', $module_id)
                    ->where('company_id', Session()->get('user_company_id'))
                    ->first();
            }
        

        return $priv;
    }
    public function hasModulePermission($module_id, $action)
    {
        $user = \App\Models\Companies::where('id', Session()->get('user_company_id'))->first();
        $priv = \App\Models\MerchantModuleMapping::where('module_id', $module_id)
                                                  ->where('merchant_id', $user->user_id)
                                                  ->where('company_id', Session()->get('user_company_id'))
                                                  ->first();

        return $priv;
    }
}
