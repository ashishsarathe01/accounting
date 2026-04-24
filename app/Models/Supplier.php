<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;
class Supplier extends Model
{
    use HasFactory;
    public function locationRates()
    {
        return $this->hasMany(SupplierLocationRates::class, 'parent_id', 'id');
    }
    public function latestLocationRate1()
    {
        return $this->hasMany(SupplierLocationRates::class, 'parent_id','id')
        // ->where('');
        ->whereIn('r_date', function ($query) {
            $query->selectRaw('MAX(r_date)')
                  ->from('supplier_location_rates as slr')
                  ->whereColumn('slr.parent_id', 'supplier_location_rates.parent_id');
        });
    }
    public function latestLocationRate()
{
    return $this->hasMany(SupplierLocationRates::class, 'parent_id', 'id')
        ->joinSub(
            DB::table('supplier_location_rates')
                ->select('parent_id', DB::raw('MAX(r_date) as max_date'))
                ->groupBy('parent_id'),
            'latest',
            function ($join) {
                $join->on('supplier_location_rates.parent_id', '=', 'latest.parent_id')
                     ->on('supplier_location_rates.r_date', '=', 'latest.max_date');
            }
        );
}
    public function account()
    {
        return $this->belongsTo(Accounts::class, 'account_id', 'id');
    }
    public function bonuses()
{
    return $this->hasMany(SupplierBonus::class, 'supplier_id', 'id');
}
}
