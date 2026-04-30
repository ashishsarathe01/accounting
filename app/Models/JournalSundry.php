<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalSundry extends Model
{
    protected $table = 'journal_sundries';

    protected $fillable = [
        'journal_id',
        'bill_sundry',
        'rate',
        'amount',
        'company_id',
        'status'
    ];
}