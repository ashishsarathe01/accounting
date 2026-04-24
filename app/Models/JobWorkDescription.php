<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\JobWork;
use App\Models\ManageItems;
use App\Models\JobWorkItemSizeMap;

class JobWorkDescription extends Model
{
    protected $table = 'job_work_descriptions';

    protected $fillable = [
        'job_work_id',
        'goods_discription',
        'item_description',
        'qty',
        'unit',
        'price',
        'amount',
        'company_id',
        'status',
        'delete',
        'created_by',
    ];

    /* =========================
       RELATIONSHIPS
    ========================== */

    public function jobWork()
    {
        return $this->belongsTo(JobWork::class, 'job_work_id');
    }

    // goods_discription stores manage_items.id
    public function item()
{
    return $this->belongsTo(ManageItems::class, 'goods_discription', 'id');
}

}
