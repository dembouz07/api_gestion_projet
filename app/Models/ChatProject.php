<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatProject extends Model
{
    use HasFactory;
    protected $guarded = [];

    // Chat lié à un projet
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // Chat contient plusieurs messages
    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
