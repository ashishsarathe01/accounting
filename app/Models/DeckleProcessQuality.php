<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeckleProcessQuality extends Model
{
    use HasFactory;
    public function items()
    {
        return $this->hasMany(DeckleItem::class, 'quality_id', 'id')->where('status', 1);
    }
    public function item_stock()
    {
        return $this->hasMany(ItemSizeStock::class, 'quality_row_id', 'id');
    }
      public function item()
    {
        return $this->belongsTo(ManageItems::class, 'item_id', 'id');
    }
      public function deckleProcess()
    {
        return $this->belongsTo(DeckleProcess::class, 'parent_id', 'id');
    }
}
