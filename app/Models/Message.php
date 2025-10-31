<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;
    protected $guarded = [];

    // Message envoyé par un user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Message appartient à un chat
    public function chatProject()
    {
        return $this->belongsTo(ChatProject::class);
    }
}
