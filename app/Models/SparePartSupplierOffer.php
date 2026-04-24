<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SparePartSupplierOffer extends Model
{
    protected $table = 'spare_part_supplier_offers';

    protected $fillable = [
        'spare_part_id',
        'account_id',
        'item_id',
        'offered_quantity',
        'offered_price',
        'company_id',
        'created_by',
    ];

    public function account()
    {
        return $this->belongsTo(Accounts::class, 'account_id');
    }

    public function item()
    {
        return $this->belongsTo(ManageItems::class, 'item_id');
    }
}
