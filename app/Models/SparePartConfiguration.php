<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SparePartConfiguration extends Model
{
    protected $table = 'spare_part_configurations';

    protected $fillable = [
        'company_id',
        'po_prefix',
        'po_start_from',
        'current_po_number',
    ];

    public $timestamps = true;
}
