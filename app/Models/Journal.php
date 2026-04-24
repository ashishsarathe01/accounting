<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_no',
        'voucher_no_prefix',
    ];

    public function journal_details(){
        return $this->hasMany('App\Models\JournalDetails', 'journal_id','id')
                     ->where('status','1');
    }
    public function sparePart()
    {
        return $this->belongsTo(
            \App\Models\SparePart::class,
            'spare_part_id',
            'id'
        );
    }
}
