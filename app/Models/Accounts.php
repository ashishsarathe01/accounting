<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accounts extends Model
{
    use HasFactory;
    protected $fillable = [
        'company_id',
        'account_name',
        'print_name',
        'under_group',
        'under_group_type',
        'under_group_s',
        'tds_section'
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];
    public function accountLedger(){
      return $this->hasMany('App\Models\AccountLedger','account_id','id');
    }
    public function otherAddress(){
      return $this->hasMany('App\Models\AccountOtherAddress','account_id','id')->where('status',1);
    }
    public function stateMaster()
    {
      return $this->belongsTo(\App\Models\State::class, 'state');
    }
}
