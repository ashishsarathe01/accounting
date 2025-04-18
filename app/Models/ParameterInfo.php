<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParameterInfo extends Model
{
    use HasFactory;
    protected $table = "parameter_info";
    public function parameterColumnName() {
        return $this->belongsTo('App\Models\ItemParameterList','parameter_col_id','id');
    }
    public function parameterColumnValues() {
        return $this->hasMany('App\Models\ParameterInfoValue','parent_id','id');
    }
}
