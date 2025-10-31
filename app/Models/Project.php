<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;
    protected $guarded = [];

    // Relation Many-to-Many avec User
    public function users()
    {
        return $this->belongsToMany(User::class, 'project_user')
            ->withTimestamps();
    }

    // Un projet a plusieurs sprints
    public function sprints()
    {
        return $this->hasMany(Sprint::class);
    }

    // Un projet a un seul Product Backlog
    public function productBacklog()
    {
        return $this->hasOne(ProductBacklog::class);
    }

    // Un projet peut avoir plusieurs discussions
    public function chats()
    {
        return $this->hasOne(ChatProject::class);
    }
}
