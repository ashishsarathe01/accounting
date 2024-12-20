<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemParameter extends Model
{
   use HasFactory;
   protected $table = "item_paremeter";
   public function parameters(){
       return $this->hasMany('App\Models\ItemParameterList', 'parent_id','id');
   }
}
