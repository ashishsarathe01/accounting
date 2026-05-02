<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyStatutorySetting extends Model
{
    protected $table = 'company_statutory_settings';

    protected $fillable = [
        'company_id',
        'gst',
        'esic',
        'tds',
        'pf'
    ];

    public function company()
    {
        return $this->belongsTo(\App\Models\Companies::class, 'company_id');
    }    
}