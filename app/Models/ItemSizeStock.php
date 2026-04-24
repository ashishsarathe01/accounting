<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemSizeStock extends Model
{
    use HasFactory;
    
    protected $fillable = [
    'item_id', 'size', 'weight', 'reel_no', 'deckle_id',
    'quality_row_id', 'bf', 'gsm', 'unit', 'sale_description_id',
    'sale_return_id', 'sale_order_id', 'sale_id', 'status',
    'company_id', 'created_by'
    ];

    public function items_stock()
    {
        return $this->hasMany(ItemStockSize::class, 'id', 'deckle_id');
    }
    public function item()
    {
        return $this->belongsTo(ManageItems::class, 'item_id');
    }
}
