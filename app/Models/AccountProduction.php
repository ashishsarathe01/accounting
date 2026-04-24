<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountProduction extends Model
{
    use HasFactory;
    public function productionDetail(){
        return $this->hasMany(AccountProductionDetail::class, 'parent_id');
    }
}
