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
}
