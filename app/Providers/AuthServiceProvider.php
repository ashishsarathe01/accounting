<?php

namespace App\Providers;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

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
        Gate::define('view-module', function (User $user, $module_id) {
            if ($user->type=="OWNER") {
                return true;
            }
            return $user->hasPrivilege($module_id, 'view');
        });

        Gate::define('create-module', function (User $user, $module_id) {
            if ($user->is_admin) return true;
            return $user->hasPrivilege($module_id, 'create');
        });

        Gate::define('update-module', function (User $user, $module_id) {
            if ($user->is_admin) return true;
            return $user->hasPrivilege($module_id, 'edit');
        });

        Gate::define('delete-module', function (User $user, $module_id) {
            if ($user->is_admin) return true;
            return $user->hasPrivilege($module_id, 'delete');
        });
        
    }
}
