<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeckleProcess extends Model
{
    use HasFactory;
    public function quality()
    {
        return $this->hasMany(DeckleProcessQuality::class, 'parent_id', 'id');
    }
}
