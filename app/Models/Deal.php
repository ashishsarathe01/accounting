<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Accounts;

class Deal extends Model
{
    use HasFactory;

    protected $table = 'manage_deal'; // explicitly set table name
    public $timestamps = false; // your table uses varchar for dates

    // Allow mass assignment
    protected $fillable = [
        'deal_no',
        'deal_type',
        'qty',
        'party_id',
        'freight',
        'comp_id',
        'manage_deal_items_id',
        'status',
        'final_complete',
        'created_at',
        'created_by',
        'updated_by',
        'updated_at',
        'completed_by',
        'completed_at'
    ];

    public function items(){
    return $this->hasMany(manage_deal_items::class, 'manage_deal_id', 'id');
}
 public function party()
    {
        return $this->belongsTo(Accounts::class, 'party_id');
    }

    

}