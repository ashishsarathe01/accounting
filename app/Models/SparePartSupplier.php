<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SparePartSupplier extends Model
{
    use HasFactory;

    protected $table = 'spare_part_suppliers';

    protected $fillable = [
        'company_id',
        'account_id',
        'status',
        'created_by',
    ];

    /**
     * Relation: Supplier Account
     */
    public function account()
    {
        return $this->belongsTo(Accounts::class, 'account_id');
    }
}
