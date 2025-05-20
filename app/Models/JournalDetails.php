<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalDetails extends Model
{
    use HasFactory;
    public function account_details(){
        return $this->belongsTo('App\Models\Accounts','account_name','id');
    }
}
