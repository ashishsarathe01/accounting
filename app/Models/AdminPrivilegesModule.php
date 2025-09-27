<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminPrivilegesModule extends Model
{
    use HasFactory;

    protected $table = 'admin_privileges_modules';
    protected $fillable = ['module_name', 'parent_id', 'status', 'created_at', 'updated_at', 'updated_by'];

    public function parent()
    {
        return $this->belongsTo(AdminPrivilegesModule::class, 'parent_id');
    }
}
