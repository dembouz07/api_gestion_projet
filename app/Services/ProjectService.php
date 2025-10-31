<?php

namespace App\Services;

use App\Models\ChatProject;
use App\Models\ProductBacklog;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ProjectService
{
    public function __construct()
    {
        $this->notificationService = new NotificationService();
    }

    public function index(){
        return Project::all();
    }

    public function show(string $id)
    {
        $project = Project::with(['users', 'productBacklog.userStories', 'sprints'])
            ->findOrFail($id);
        return $project;
    }

    public function store(array $request){
        $project = Project::create($request);

        ProductBacklog::create([
            'project_id' => $project->id,
        ]);

        ChatProject::create([
            'project_id' => $project->id,
        ]);

        return $project;
    }

    public function update(array $request, string $id){
        $project = Project::findOrFail($id);
        $project->update($request);
        return $project;
    }

    public function destroy(string $id){
        $project = Project::findOrFail($id);
        $project->delete();
        return response()->json(['message' => 'Projet supprimé avec succès'], 200);
    }

    public function addUserToProject(string $id, array $userIds)
    {
        $project = Project::findOrFail($id);

        $users = User::whereIn('id', $userIds)->get();

        if ($users->count() !== count($userIds)) {
            throw new \Exception('Un ou plusieurs utilisateurs sont introuvables');
        }

        $existingUserIds = $project->users()->pluck('user_id')->toArray();

        $newUserIds = array_diff($userIds, $existingUserIds);

        $project->users()->syncWithoutDetaching($userIds);

        if (!empty($newUserIds)) {
            $addedBy = auth()->user();

            foreach ($newUserIds as $userId) {
                $this->notificationService->create(
                    $userId,
                    'Added to Project',
                    "You have been added to the project '{$project->name}' by {$addedBy->name}"
                );
            }
        }

        return $project->load('users');
    }

    public function removeUserFromProject(string $id, array $userIds)
    {
        $project = Project::findOrFail($id);
        $project->users()->detach($userIds);

        return $project->load('users');
    }

    public function getProjectMembers(string $id)
    {
        $project = Project::findOrFail($id);
        return $project->users;
    }

    public function getProjectProgress(string $projectId)
    {
        $project = Project::with(['productBacklog.userStories.tasks'])
            ->findOrFail($projectId);

        $backlog = $project->productBacklog;
        if (!$backlog) {
            return ['progress' => 0, 'message' => 'Aucun backlog trouvé'];
        }

        $userStories = $backlog->userStories;
        $allTasks = $userStories->flatMap(function($us) {
            return $us->tasks;
        });

        $totalTasks = $allTasks->count();
        $completedTasks = $allTasks->where('status', 'completed')->count();

        $progress = $totalTasks > 0
            ? round($completedTasks / $totalTasks * 100, 2)
            : 0;

        return [
            'project_id' => $projectId,
            'progress' => $progress,
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'remaining_tasks' => $totalTasks - $completedTasks
        ];
    }

    public function getProjectsForAuthenticatedUser()
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            $projects = Project::with('users')->get();
        } else {
            // Pour les Project Managers et membres d'équipe
            $projects = Project::whereHas('users', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
                ->orWhere('project_manager_id', $user->id)
                ->with('users')
                ->get();
        }

        // Calculer le progress pour chaque projet
        $projects = $projects->map(function ($project) {
            $progress = $this->calculateProjectProgress($project);
            $project->progress = $progress;
            return $project;
        });

        return ['data' => $projects];
    }

    private function calculateProjectProgress($project)
    {
        $backlog = $project->productBacklog()->first();

        if (!$backlog) {
            return 0;
        }

        $userStories = $backlog->userStories()->get();

        if ($userStories->count() === 0) {
            return 0;
        }

        $completedStories = $userStories->where('status', 'completed')->count();
        return round(($completedStories / $userStories->count()) * 100);
    }

    public function getProjectBacklogWithStats(string $projectId)
    {
        $project = Project::findOrFail($projectId);

        $backlog = $project->productBacklog;

        if (!$backlog) {
            return [
                'project' => $project,
                'userStories' => [],
                'stats' => [
                    'total' => 0,
                    'in_backlog' => 0,
                    'in_sprint' => 0,
                    'completed' => 0,
                ]
            ];
        }

        // Récupérer les user stories avec leurs tâches
        $userStories = $backlog->userStories()
            ->with(['tasks'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculer le progress pour chaque user story
        $userStories = $userStories->map(function ($story) {
            if ($story->tasks && $story->tasks->count() > 0) {
                $completedTasks = $story->tasks->where('status', 'completed')->count();
                $story->progress = round(($completedTasks / $story->tasks->count()) * 100);
            } else {
                $story->progress = 0;
            }
            return $story;
        });

        // Calculer les stats
        $stats = [
            'total' => $userStories->count(),
            'in_backlog' => $userStories->where('sprint_id', null)->count(),
            'in_sprint' => $userStories->whereNotNull('sprint_id')->count(),
            'completed' => $userStories->where('status', 'completed')->count(),
        ];

        return [
            'project' => $project,
            'userStories' => $userStories,
            'stats' => $stats,
        ];
    }


}
