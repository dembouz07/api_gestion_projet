<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserStoryRequest;
use App\Services\UserStoryService;
use App\Services\ElasticsearchService;
use App\Models\UserStory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserStoryController extends Controller
{
    protected $userStoryService;
    protected $elasticsearchService;

    public function __construct(ElasticsearchService $elasticsearchService)
    {
        $this->userStoryService = new UserStoryService();
        $this->elasticsearchService = $elasticsearchService;
    }

    /**
     * Récupérer toutes les user stories d'un backlog
     */
    public function getBacklogUserStories(string $backlogId)
    {
        try {
            $this->elasticsearchService->logUserActivity('viewed_backlog_user_stories', [
                'backlog_id' => $backlogId,
            ]);

            $userStories = $this->userStoryService->getBacklogUserStories($backlogId);

            $this->elasticsearchService->logMetric('backlog_stories_count', [
                'backlog_id' => $backlogId,
                'stories_count' => count($userStories),
            ]);

            return response()->json($userStories, 200);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve backlog user stories', [
                'backlog_id' => $backlogId,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            throw $e;
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $this->elasticsearchService->logUserActivity('viewed_user_stories_list');

            $userStories = $this->userStoryService->index();

            return response()->json($userStories, 200);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve user stories', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            throw $e;
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserStoryRequest $request)
    {
        $startTime = microtime(true);

        try {
            $userStory = $this->userStoryService->store($request->validated());

            $duration = (microtime(true) - $startTime) * 1000;

            // Log l'activité
            $this->elasticsearchService->logUserActivity('user_story_created', [
                'user_story_id' => $userStory->id,
                'title' => $userStory->title,
                'product_backlog_id' => $userStory->product_backlog_id,
                'sprint_id' => $userStory->sprint_id,
            ]);

            // Log la métrique
            $this->elasticsearchService->logMetric('user_story_creation', [
                'user_story_id' => $userStory->id,
                'has_sprint' => !is_null($userStory->sprint_id),
                'created_by' => auth()->id(),
            ]);

            // Log la performance
            $this->elasticsearchService->logPerformance('create_user_story', $duration, [
                'user_story_id' => $userStory->id,
            ]);

            Log::info('User story created successfully', [
                'user_story_id' => $userStory->id,
                'user_id' => auth()->id(),
            ]);

            return response()->json($userStory, 201);

        } catch (\Exception $e) {
            Log::error('Failed to create user story', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            $this->elasticsearchService->logMetric('user_story_creation', [
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $this->elasticsearchService->logUserActivity('viewed_user_story_details', [
                'user_story_id' => $id,
            ]);

            $userStory = $this->userStoryService->show($id);

            return response()->json($userStory, 200);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve user story', [
                'user_story_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            throw $e;
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserStoryRequest $request, string $id)
    {
        $startTime = microtime(true);

        try {
            $oldUserStory = $this->userStoryService->show($id);
            $userStory = $this->userStoryService->update($request->validated(), $id);

            $duration = (microtime(true) - $startTime) * 1000;

            // Détecter les changements importants
            $sprintChanged = $oldUserStory->sprint_id !== $userStory->sprint_id;
            $statusChanged = $oldUserStory->status !== $userStory->status;

            $this->elasticsearchService->logUserActivity('user_story_updated', [
                'user_story_id' => $id,
                'title' => $userStory->title,
                'sprint_changed' => $sprintChanged,
                'status_changed' => $statusChanged,
            ]);

            // Log si déplacé vers/depuis un sprint
            if ($sprintChanged) {
                $this->elasticsearchService->logMetric('user_story_sprint_change', [
                    'user_story_id' => $id,
                    'old_sprint_id' => $oldUserStory->sprint_id,
                    'new_sprint_id' => $userStory->sprint_id,
                    'action' => $userStory->sprint_id ? 'added_to_sprint' : 'removed_from_sprint',
                ]);
            }

            // Log si statut changé
            if ($statusChanged) {
                $this->elasticsearchService->logMetric('user_story_status_change', [
                    'user_story_id' => $id,
                    'old_status' => $oldUserStory->status,
                    'new_status' => $userStory->status,
                ]);
            }

            $this->elasticsearchService->logPerformance('update_user_story', $duration, [
                'user_story_id' => $id,
            ]);

            Log::info('User story updated successfully', [
                'user_story_id' => $id,
                'user_id' => auth()->id(),
            ]);

            return response()->json($userStory, 200);

        } catch (\Exception $e) {
            Log::error('Failed to update user story', [
                'user_story_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            throw $e;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $userStory = $this->userStoryService->show($id);

            $this->elasticsearchService->logUserActivity('user_story_deleted', [
                'user_story_id' => $id,
                'title' => $userStory->title ?? null,
            ]);

            $this->elasticsearchService->logMetric('user_story_deletion', [
                'user_story_id' => $id,
                'deleted_by' => auth()->id(),
            ]);

            Log::warning('User story deleted', [
                'user_story_id' => $id,
                'user_id' => auth()->id(),
            ]);

            $this->userStoryService->destroy($id);

            return response()->json(['message' => 'User story supprimée avec succès'], 200);

        } catch (\Exception $e) {
            Log::error('Failed to delete user story', [
                'user_story_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            throw $e;
        }
    }

    /**
     * Calcule et met à jour la progression de toutes les user stories
     */
    public function getAllProgres()
    {
        $startTime = microtime(true);

        try {
            $this->elasticsearchService->logUserActivity('calculated_all_user_stories_progress');

            $progress = $this->userStoryService->getAllProgress();

            $duration = (microtime(true) - $startTime) * 1000;

            $this->elasticsearchService->logPerformance('calculate_all_progress', $duration, [
                'stories_count' => count($progress),
            ]);

            return response()->json($progress, 200);

        } catch (\Exception $e) {
            Log::error('Failed to calculate progress', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            throw $e;
        }
    }

    /**
     * Toutes les user stories du PO connecté par projet
     */
    public function getMyProjectUserStories(string $projectId)
    {
        try {
            $this->elasticsearchService->logUserActivity('viewed_my_project_user_stories', [
                'project_id' => $projectId,
            ]);

            $userStories = $this->userStoryService->getUserStoriesByProjectAndProductOwner($projectId);

            return response()->json($userStories, 200);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve project user stories', [
                'project_id' => $projectId,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            throw $e;
        }
    }

    /**
     * Get all user stories of a project (for task creation)
     */
    public function getProjectUserStories(string $projectId)
    {
        try {
            $this->elasticsearchService->logUserActivity('viewed_project_user_stories', [
                'project_id' => $projectId,
            ]);

            $userStories = UserStory::whereHas('productBacklog', function ($query) use ($projectId) {
                $query->where('project_id', $projectId);
            })
                ->with(['sprint', 'tasks', 'productBacklog'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json($userStories, 200);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve project user stories', [
                'project_id' => $projectId,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Error retrieving user stories',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
