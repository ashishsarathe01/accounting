<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PrivilegesModule;
use Illuminate\Auth\Access\HandlesAuthorization;

class PrivilegesModulePolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
    public function view(User $user, PrivilegesModule $module)
    { 
        if ($user->type=="OWNER") {
            return true;
        }
        return $user->hasPrivilege($module->id, 'view');
    }

    public function create(User $user, PrivilegesModule $module)
    {
        return $user->hasPrivilege($module->id, 'create');
    }

    public function update(User $user, PrivilegesModule $module)
    {
        return $user->hasPrivilege($module->id, 'edit');
    }

    public function delete(User $user, PrivilegesModule $module)
    {
        return $user->hasPrivilege($module->id, 'delete');
    }
}
