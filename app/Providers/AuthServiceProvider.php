<?php

namespace App\Providers;
use App\Models\User;
use App\Models\Companies;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Session;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
        \App\Models\PrivilegesModule::class => \App\Policies\PrivilegesModulePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     * 
     */
    public function boot()
    {
        $this->registerPolicies();
        Gate::define('action-module', function (User $user, $module_id) {
            $company_id = Session::get('user_company_id');
            if (Session()->get('user_type')=="OWNER" && !Session::get('admin_id')) {
                return \App\Models\MerchantPrivilegeMapping::where('merchant_id', $user->id)
                                                            ->where('company_id', $company_id)
                                                            ->where('module_id', $module_id)
                                                            ->exists();
                //return true;
            }
            $owner_id = Companies::select('user_id')
                            ->where('id', $company_id)
                            ->first();
            $owner_id_privilege = \App\Models\MerchantPrivilegeMapping::where('merchant_id', $owner_id->user_id)
                                                                        ->where('company_id', $company_id)
                                                                        ->where('module_id', $module_id)
                                                                        ->exists();
            if($owner_id_privilege){
                return $user->hasPrivilege($module_id, 'view');
            }else{
                return false;
            }
            //return $user->hasPrivilege($module_id, 'delete');
        });
        Gate::define('view-module', function (User $user, $module_id) {
            $company_id = Session::get('user_company_id');
            if (Session()->get('user_type')=="OWNER" && !Session::get('admin_id')) {
                return \App\Models\MerchantPrivilegeMapping::where('merchant_id', $user->id)
                ->where('company_id', $company_id)
                ->where('module_id', $module_id)
                ->exists();
                //return true;
            }
            $owner_id = Companies::select('user_id')
                            ->where('id', $company_id)
                            ->first();
            $owner_id_privilege = \App\Models\MerchantPrivilegeMapping::where('merchant_id', $owner_id->user_id)
                                                                        ->where('company_id', $company_id)
                                                                        ->where('module_id', $module_id)
                                                                        ->exists();
            if($owner_id_privilege){
                return $user->hasPrivilege($module_id, 'view');
            }else{
                return false;
            }
            //return $user->hasPrivilege($module_id, 'view');
        });

        Gate::define('create-module', function (User $user, $module_id) {
            if ($user->type=="OWNER" && !Session::get('admin_id')) {
                return true;
            }
            return $user->hasPrivilege($module_id, 'create');
        });

        Gate::define('update-module', function (User $user, $module_id) {
            if ($user->type=="OWNER" && !Session::get('admin_id')) {
                return true;
            }
            return $user->hasPrivilege($module_id, 'edit');
        });

        Gate::define('delete-module', function (User $user, $module_id) {
            if ($user->type=="OWNER") {
                return true;
            }
            return $user->hasPrivilege($module_id, 'delete');
        });
        Gate::define('module-permission', function (User $user, $module_id) {            
            return $user->hasModulePermission($module_id, 'module-permission');
        });
    }
}
