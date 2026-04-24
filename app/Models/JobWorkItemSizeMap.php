<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\JobWorkDescription;
use App\Models\ItemSizeStock;

class JobWorkItemSizeMap extends Model
{
    protected $table = 'job_work_item_size_map';

    protected $fillable = [
        'company_id',
        'job_work_id',
        'job_work_description_id',
        'item_size_stock_id',
    ];

    public $timestamps = false;

    /* =========================
       RELATIONSHIPS
    ========================== */

    public function description()
    {
        return $this->belongsTo(JobWorkDescription::class, 'job_work_description_id');
    }

    public function sizeStock()
    {
        return $this->belongsTo(ItemSizeStock::class, 'item_size_stock_id');
    }
}
