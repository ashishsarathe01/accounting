<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SparePartTermsCondition extends Model
{
    use SoftDeletes;

    protected $table = 'spare_part_terms_conditions';

    protected $fillable = [
        'company_id',
        'term_text',
        'sequence',
        'status',
    ];

    protected $dates = ['deleted_at'];

    public $timestamps = true;
}
