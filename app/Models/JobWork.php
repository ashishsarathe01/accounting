<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\JobWorkDescription;
use App\Models\Accounts;

class JobWork extends Model
{
    protected $table = 'job_works';

    protected $fillable = [
        'company_id',
        'series_no',
        'date',
        'voucher_no_prefix',
        'voucher_no',
        'party_id',
        'material_center',
        'total',
        'vehicle_no',
        'transport_name',
        'reverse_charge',
        'gr_rr_no',
        'station',
        'status',
        'delete',
        'created_by',
    ];

    /* =========================
       RELATIONSHIPS
    ========================== */

    public function descriptions()
    {
        return $this->hasMany(JobWorkDescription::class, 'job_work_id');
    }

    public function party()
    {
        return $this->belongsTo(Accounts::class, 'party_id');
    }
}
