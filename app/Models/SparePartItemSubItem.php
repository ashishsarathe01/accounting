<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SparePartItemSubItem extends Model
{
    use HasFactory;
    public function item()
    {
        return $this->belongsTo(SparePartItem::class, 'spare_part_item_id');
    }

    /**
     * Sizes / reels for this sub-item
     */
    public function sizes()
    {
        return $this->hasMany(SparePartItemSubItemSize::class, 'sub_item_id');
    }
}
