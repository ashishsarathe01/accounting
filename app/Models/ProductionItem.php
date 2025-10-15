<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionItem extends Model
{
    use HasFactory;

    protected $table = 'production_items';
    protected $fillable = ['item_id', 'bf', 'gsm', 'speed', 'status', 'company_id', 'created_by'];

    public $timestamps = false;

    public function item()
    {
        return $this->belongsTo(ManageItems::class, 'item_id', 'id');
    }
}
