<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleReturnWithoutGstEntry extends Model
{
    use HasFactory;
    public $table = "sale_return_without_gst_entry";
    public $timestamps = false;
}
