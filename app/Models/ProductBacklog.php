<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductBacklog extends Model
{
    use HasFactory;
    protected $guarded = [];

    // Le backlog appartient à un projet
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // Contient plusieurs User Stories
    public function userStories()
    {
        return $this->hasMany(UserStory::class);
    }
}
