<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RejectedGstr2b extends Model
{
    use HasFactory;
    protected $table = 'rejected_gstr2b';
    public $guarded = [];
}
