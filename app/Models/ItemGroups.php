<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemGroups extends Model
{
    use HasFactory;
    public function parameters(){
        return $this->hasMany('App\Models\ItemGroupParameterList', 'parent_id','id')
                     ->where('status',1);
    }
}
