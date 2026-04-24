<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SparePartItem extends Model
{
    use HasFactory;
    protected $table = 'spare_part_items';

    protected $fillable = [
        'spare_part_id',
        'item_id',
        'price',
        'unit',
        'quantity',
        'narration',
        'status',
        'company_id',
        'required_date',
    ];

    public function sparePart()
    {
        return $this->belongsTo(SparePart::class, 'spare_part_id');
    }

    /**
     * Sub-items / GSM groups for this item
     */
    public function subItems()
    {
        return $this->hasMany(SparePartItemSubItem::class, 'spare_part_item_id');
    }
    public function sizes()
    {
        return $this->hasMany(SparePartItemSubItemSize::class, 'spare_part_item_id');
    }
    public function item()
    {
        return $this->belongsTo(ManageItems::class, 'item_id', 'id');
    }
}
