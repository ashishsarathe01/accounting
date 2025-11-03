<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemSizeStock extends Model
{
    use HasFactory;
    public function items_stock()
    {
        return $this->hasMany(ItemStockSize::class, 'id', 'deckle_id');
    }
    public function item()
    {
        return $this->belongsTo(ManageItems::class, 'item_id');
    }
}
