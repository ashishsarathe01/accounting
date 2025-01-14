<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemParameterList extends Model
{
    use HasFactory;
    protected $table = "item_paremeter_list";
    public $timestamps = false;
    public function predefinedValue(){
        return $this->hasMany('App\Models\ItemParameterPredefinedValue', 'parent_id','id');
    }
}
