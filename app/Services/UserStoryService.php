<?php

namespace App\Services;

use App\Models\Task;
use App\Models\UserSTory;

class UserStoryService
{

    public function getBacklogUserStories(string $backlogId)
    {
        return UserStory::where('product_backlog_id', $backlogId)
            ->with(['sprint', 'tasks', 'increments'])
            ->get();
    }

    public function index()
    {
        return UserStory::with(['productBacklog', 'sprint', 'tasks', 'increments'])->get();
    }

    public function show(string $id){
        return UserSTory::findOrFail($id);
    }

    public function store(array $request){
        return UserSTory::create($request);
    }

    public function update(array $request, string $id){
        $userStory = UserSTory::findOrFail($id);
        $userStory->update($request);
        return $userStory;
    }

    public function destroy(string $id){
        $userStory = UserSTory::findOrFail($id);
        $userStory->delete();
        return response()->json(['message' => 'UserSTory supprimé avec succès'], 200);
    }

    public function getAllProgress()
    {
        $userStories = UserStory::all();
        $results = [];

        foreach ($userStories as $story) {
            $tasks = Task::where('user_story_id', $story->id)->get();

            if ($tasks->count() === 0) {
                $progress = 0;
            } else {
                $completedTasks = $tasks->where('status', 'completed')->count();
                $progress = round(($completedTasks / $tasks->count()) * 100, 2);
            }

            if ($progress === 0) {
                $story->status = 'pending';
            } elseif ($progress < 100) {
                $story->status = 'active';
            } else {
                $story->status = 'completed';
            }

            $story->save();

            $results[] = [
                'user_story_id' => $story->id,
                'title' => $story->title,
                'progress' => $progress,
                'status' => $story->status,
            ];
        }

        return $results;
    }

    public function getUserStoriesByProjectAndProductOwner(string $projectId)
    {
        $productOwnerId = auth()->id();

        return UserStory::where('created_by', $productOwnerId)
            ->whereHas('productBacklog', function ($query) use ($projectId) {
                $query->where('project_id', $projectId);
            })
            ->with(['sprint', 'tasks', 'increments', 'productBacklog'])
            ->orderBy('created_at', 'desc')
            ->get();
    }


}
