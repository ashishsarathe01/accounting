<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskComment extends Model
{
    protected $table = 'task_comments';

    protected $fillable = [
        'task_id',
        'user_id',
        'comment'
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
