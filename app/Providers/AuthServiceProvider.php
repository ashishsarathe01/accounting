<?php

namespace App\Providers;
use App\Models\User;
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
     */
    public function boot()
    {
        $this->registerPolicies();
        Gate::define('action-module', function (User $user, $module_id) {
            if ($user->type=="OWNER" && !Session::get('admin_id')) {
                return true;
            }
            
            return $user->hasPrivilege($module_id, 'delete');
        });
        Gate::define('view-module', function (User $user, $module_id) {
            if ($user->type=="OWNER" && !Session::get('admin_id')) {
                return true;
            }
            return $user->hasPrivilege($module_id, 'view');
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
