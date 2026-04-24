<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SparePart extends Model
{
    use HasFactory;
    protected $table = 'spare_parts';

    protected $fillable = [
        'root_spare_part_id',   // ✅ REQUIRED
        'order_sequence',       // ✅ REQUIRED
        'account_id',
        'source',
        'status',
        'company_id',
        'created_by',
        'updated_by',
        'po_number',
        'po_date',
        'bill_to_account_id',
        'ship_to_account_id',
        'bill_to_company_id',
        'ship_to_company_id',
        'po_narration',
        'freight',
        'department',
    'purpose',
    'department_head',
    'requirement_by',
    'hod',
    'approved_for_quotation',

    ];

    public function items()
    {
        return $this->hasMany(SparePartItem::class, 'spare_part_id');
    }
    public function account()
    {
        return $this->hasOne(Accounts::class, 'id','account_id');
    }
    public function billToAccount()
    {
        return $this->belongsTo(Accounts::class, 'bill_to_account_id');
    }

    public function shipToAccount()
    {
        return $this->belongsTo(Accounts::class, 'ship_to_account_id');
    }

    public function billToCompany()
    {
        return $this->belongsTo(Companies::class, 'bill_to_company_id');
    }

    public function shipToCompany()
    {
        return $this->belongsTo(Companies::class, 'ship_to_company_id');
    }

    public function purchase()
    {
        return $this->hasOne(\App\Models\Purchase::class, 'spare_part_id', 'id');
    }
    public function getFreightTextAttribute()
    {
        return $this->freight == '1' ? 'Yes' : 'No';
    }
    public function getBillToNameAttribute()
    {
        if ($this->bill_to_company_id) {
            return optional(
                \App\Models\Companies::find($this->bill_to_company_id)
            )->company_name;
        }

        if ($this->bill_to_account_id) {
            return optional(
                \App\Models\Accounts::find($this->bill_to_account_id)
            )->account_name;
        }

        return '-';
    }

    public function getShipToNameAttribute()
    {
        if ($this->ship_to_company_id) {
            return optional(
                \App\Models\Companies::find($this->ship_to_company_id)
            )->company_name;
        }

        if ($this->ship_to_account_id) {
            return optional(
                \App\Models\Accounts::find($this->ship_to_account_id)
            )->account_name;
        }

        return '-';
    }
    public function supplierOffers()
    {
        return $this->hasMany(\App\Models\SparePartSupplierOffer::class, 'spare_part_id');
    }
    public function user()
    {
        return $this->hasOne(User::class, 'id','created_by');
    }
    public function approvedBy()
    {
        return $this->hasOne(User::class, 'id','approved_by');
    }

}
