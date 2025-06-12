<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrivilegesModule extends Model
{
    use HasFactory;
    public function parent()
    {
        return $this->belongsTo(PrivilegesModule::class, 'parent_id');
    }
}
