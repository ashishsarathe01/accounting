<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FuelSupplier extends Model
{
    use HasFactory;
    public function itemRates()
    {
        return $this->hasMany(FuelSupplierRate::class, 'parent_id', 'id');
    }    
    public function account()
    {
        return $this->belongsTo(Accounts::class, 'account_id', 'id');
    }
}
