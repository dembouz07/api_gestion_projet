<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TaskService
{
    public function __construct()
    {
        $this->notificationService = new NotificationService();
    }
    public function index()
    {
        return Task::all();
    }

    public function show(string $id){
        return Task::findOrFail($id);
    }

    public function store(array $request)
    {
        $task = Task::create($request);

        // Créer une notification si un utilisateur est assigné à la tâche
        if (isset($request['assigned_to']) && $request['assigned_to']) {
            $assignedBy = auth()->user();

            // Récupérer l'utilisateur assigné
            $assignedUser = User::find($request['assigned_to']);

            if ($assignedUser) {
                $this->notificationService->create(
                    $assignedUser->id,
                    'New Task Assigned',
                    "You have been assigned a new task '{$task->title}' by {$assignedBy->name}"
                );
            }
        }

        return $task;
    }

    public function update(array $request, string $id){
        $task = Task::findOrFail($id);
        $task->update($request);
        return $task;
    }

    public function destroy(string $id){
        $task = Task::findOrFail($id);
        $task->delete();
        return response()->json(['message' => 'Tache supprimé avec succès'], 200);
    }

    public function getByUser(int $userId)
    {
        return Task::where('user_id', $userId)->get();
    }

    public function getUserTasksAndStats()
    {
        $userId = Auth::id();

        $tasks = Task::where('assigned_to', $userId)->get();

        $total = $tasks->count();
        $pending = $tasks->where('status', 'pending')->count();
        $active = $tasks->where('status', 'active')->count();
        $completed = $tasks->where('status', 'completed')->count();

        return [
            'user_id' => $userId,
            'total_tasks' => $total,
            'pending_tasks' => $pending,
            'active_tasks' => $active,
            'completed_tasks' => $completed,
            'tasks' => $tasks,
        ];
    }
}
