<?php

namespace App\Services;

use App\Models\Message;

class MessageService
{
    public function index()
    {
        return Message::all();
    }

    public function show(string $id){
        return Message::findOrFail($id);
    }

    public function store(array $request){
        return Message::create($request);
    }

    public function update(array $request, string $id){
        $message = Message::findOrFail($id);
        $message->update($request);
        return $message;
    }

    public function destroy(string $id){
        $message = Message::findOrFail($id);
        $message->delete();
        return response()->json(['message' => 'Message supprimé avec succès'], 200);
    }

    public function getMessagesByChatProject(string $chatProjectId)
    {
        return Message::where('chat_project_id', $chatProjectId)
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();
    }
}
