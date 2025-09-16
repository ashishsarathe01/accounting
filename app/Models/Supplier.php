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
    public function latestLocationRate()
    {
        return $this->hasMany(SupplierLocationRates::class, 'parent_id','id')
        // ->where('');
        ->whereIn('r_date', function ($query) {
            $query->selectRaw('MAX(r_date)')
                  ->from('supplier_location_rates as slr')
                  ->whereColumn('slr.parent_id', 'supplier_location_rates.parent_id');
        });
    }
    public function account()
    {
        return $this->belongsTo(Accounts::class, 'account_id', 'id');
    }
}
