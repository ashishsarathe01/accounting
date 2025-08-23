<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;
    public function locationRates()
    {
        return $this->hasMany(SupplierLocationRates::class, 'parent_id', 'id');
    }
    public function account()
    {
        return $this->belongsTo(Accounts::class, 'account_id', 'id');
    }
}
