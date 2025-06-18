<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturn extends Model
{
    use HasFactory;
    public function purchaseReturnDescriptions()
{
    return $this->hasMany(PurchaseReturnDescription::class, 'purchase_return_id');
}

public function purchaseReturnSundries()
{
    return $this->hasMany(PurchaseReturnSundry::class, 'purchase_return_id');
}

}
