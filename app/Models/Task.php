<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;

    protected $table = 'tasks';

    protected $fillable = [
        'company_id',
        'created_by',
        'assigned_to',
        'title',
        'description',
        'priority',
        'status',
        'deadline',
        'completed_at'
    ];

    protected $dates = [
        'deadline',
        'completed_at',
        'deleted_at'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Task Creator
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Task Assignee
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Task Comments
    public function comments()
    {
        return $this->hasMany(TaskComment::class);
    }

    // Task Activities (Timeline)
    public function activities()
    {
        return $this->hasMany(TaskActivity::class)->latest();
    }
}
