<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserStory extends Model
{
    use HasFactory;
    protected $guarded = [];

    // Chaque US appartient au backlog
    public function productBacklog()
    {
        return $this->belongsTo(ProductBacklog::class);
    }

    // Une US peut être liée à un sprint (ou pas)
    public function sprint()
    {
        return $this->belongsTo(Sprint::class);
    }

    // Une US contient plusieurs tâches
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    // Une User Story peut avoir plusieurs incréments
    public function increments()
    {
        return $this->hasMany(Increment::class);
    }
}
