<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobWorkInvoiceTermCondition extends Model
{
    use HasFactory;

    protected $table = 'job_work_invoice_term_conditions';

    protected $fillable = [
        'parent_id',
        'term',
        'status',
        'company_id'
    ];

    public function configuration()
    {
        return $this->belongsTo(JobWorkInvoiceConfiguration::class, 'parent_id');
    }
}