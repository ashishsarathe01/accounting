<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Session;
class AccountGroups extends Model
{
   use HasFactory;
   protected $table = 'account_groups';
   
   protected $fillable = [
    'company_id',
    'name',
    'primary',
    'heading',
    'heading_type',
    'bs_profile',
    'name_as_sch',
    'primary_as_sch',
    'heading_as_sch',
    'heading_as_sch_type',
    'bs_profile_as_sch',
    'stock_in_hand',
    'status',
    'delete',
    'created_by',
    'updated_by',
    'deleted_by',
    'created_at',
    'updated_at',
    'deleted_at',
];

   public function account(){
      return $this->hasMany(Accounts::class,'under_group','id')
                  ->where('delete','=', '0')
                  ->where('status','=', '1')
                  ->where('under_group_type','=', 'group')
                  ->whereIn('company_id',[0,Session::get('user_company_id')]);
   }
   public function accountUnderGroup(){
      return $this->hasMany(AccountGroups::class,'heading','id')
                  ->where('delete','=', '0')
                  ->where('status','=', '1')
                  ->where('heading_type','=', 'group')
                  ->whereIn('company_id',[0,Session::get('user_company_id')]);
   }
   public function children()
   {
      return $this->hasMany(AccountGroups::class, 'heading')
         ->where('status', '1')
         ->where('delete', '0');
   }

   public function childrenRecursive()
   {
      return $this->children()->with([
         'childrenRecursive',
         'account.accountLedger'
      ]);
   }
}
