<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleOrder extends Model
{
    use HasFactory;

    // Explicitly tell Laravel to use the 'sales' table
    protected $table = 'sales';

    // (Optional) Fillable columns
    protected $fillable = [
        'company_id',
        'series_no',
        'date',
        'voucher_no_prefix',
        'voucher_no',
        'party',
        'material_center',
        'tax_rate',
        'taxable_amt',
        'tax',
        'total',
        'self_vehicle',
        'vehicle_no',
        'transport_name',
        'reverse_charge',
        'gr_pr_no',
        'station',
        'ewaybill_no',
        'address_id',
        'billing_name',
        'billing_address',
        'billing_state',
        'billing_pincode',
        'billing_gst',
        'billing_pan',
        'shipping_name',
        'shipping_address',
        'shipping_state',
        'shipping_pincode',
        'shipping_gst',
        'shipping_pan',
        'merchant_gst',
        'financial_year',
        'einvoice_response',
        'e_invoice_status',
        'einvoice_by',
        'eway_bill_response',
        'e_waybill_status',
        'eway_bill_by',
        'entry_source',
        'status',
        'delete',
        'created_by',
        'updated_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
}
