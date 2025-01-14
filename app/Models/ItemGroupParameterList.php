<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemGroupParameterList extends Model
{
    use HasFactory;
    protected $table = "item_group_paremeter_list";
    public $timestamps = false;
    public function predefinedValue(){
        return $this->hasMany('App\Models\ItemGroupParameterPredefinedValue', 'parent_id','id')
                     ->where('status',1);
    }
}
