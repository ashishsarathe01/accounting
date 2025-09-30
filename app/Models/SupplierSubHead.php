<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierSubHead extends Model
{
    use HasFactory;
    public function group()
    {
        return $this->belongsTo(ItemGroups::class, 'group_id');
    }
    public function report()
    {
        return $this->hasMany(SupplierPurchaseReport::class, 'head_id');
    }
}
