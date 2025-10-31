<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Increment extends Model
{
    use HasFactory;
    protected $guarded = [];

    // Chaque incrément appartient à une User Story
    public function userStory()
    {
        return $this->belongsTo(UserStory::class);
    }
}
