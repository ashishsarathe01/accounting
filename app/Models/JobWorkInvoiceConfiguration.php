<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobWorkInvoiceConfiguration extends Model
{
    use HasFactory;

    protected $table = 'job_work_invoice_configurations';

    protected $fillable = [
        'company_logo_status',
        'company_logo',
        'logo_position_left',
        'logo_position_right',
        'bank_detail_status',
        'bank_name',
        'term_status',
        'signature_status',
        'signature',
        'company_id',
        'invoice_header_text',
        'company_name_color',
        'address_color',
        'purchase_order_status',
        'purchase_order_info_show_in_ledger',
        'show_description',
        'show_item_name',
        'company_name_font_size'
    ];

    public function terms()
    {
        return $this->hasMany(JobWorkInvoiceTermCondition::class, 'parent_id');
    }
    public function bank(){
        return $this->hasOne(Bank::class,'id','bank_name');
    }
}