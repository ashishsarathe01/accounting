<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigurationSetting extends Model
{
    protected $table = 'configuration_settings';

    protected $fillable = [
        'company_id',
        'module',
        'config_json',
    ];

    protected $casts = [
        'config_json' => 'array',
    ];
}
