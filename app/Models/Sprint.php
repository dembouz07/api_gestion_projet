<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sprint extends Model
{
    use HasFactory;
    protected $guarded = [];

    // Un sprint appartient à un projet
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // Un sprint contient plusieurs user stories
    public function userStories()
    {
        return $this->hasMany(UserStory::class);
    }

}
