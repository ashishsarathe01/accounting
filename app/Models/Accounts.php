<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accounts extends Model
{
    use HasFactory;
    public function accountLedger(){
      return $this->hasMany('App\Models\AccountLedger','account_id','id');
    }
}
