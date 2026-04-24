<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TdsSection extends Model
{
    protected $table = 'tds_sections';

    protected $fillable = [
        'section',
        'description',
        'rate_individual_huf',
        'rate_others',
        'single_transaction_limit',
        'aggregate_transaction_limit',
        'applicable_on',
        'repeated_transaction_applicable',
        'applicable_when',
        'exemption_applicable',
    ];
}