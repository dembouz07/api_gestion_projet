<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;
    protected $guarded = [];

    // Chaque tâche appartient à une User Story
    public function userStory()
    {
        return $this->belongsTo(UserStory::class);
    }

    // Une tâche peut être assignée à un user
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
