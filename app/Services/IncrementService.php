<?php

namespace App\Services;

use App\Models\Increment;

class IncrementService
{
    public function index()
    {
        return Increment::all();
    }

    public function show(string $id){
        return Increment::findOrFail($id);
    }

    public function store(array $request){
        return Increment::create($request);
    }

    public function update(array $request, string $id){
        $increment = Increment::findOrFail($id);
        $increment->update($request);
        return $increment;
    }

    public function destroy(string $id){
        $increment = Increment::findOrFail($id);
        $increment->delete();
        return response()->json(['message' => 'Increment supprimé avec succès'], 200);
    }

    public function getByUserStory(string $userStoryId)
    {
        return Increment::where('user_story_id', $userStoryId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
