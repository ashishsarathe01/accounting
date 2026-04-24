<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskActivity extends Model
{
    protected $table = 'task_activities';

    public $timestamps = false; // only created_at

    protected $fillable = [
        'task_id',
        'user_id',
        'action',
        'old_value',
        'new_value',
        'created_at'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Belongs to Task
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    // Belongs to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
