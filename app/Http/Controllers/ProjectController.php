<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectRequest;
use App\Http\Requests\ProjectUserRequest;
use App\Services\ProjectService;
use App\Services\ElasticsearchService;
use Illuminate\Support\Facades\Log;

class ProjectController extends Controller
{
    protected $projectService;
    protected $elasticsearchService;

    public function __construct(ElasticsearchService $elasticsearchService)
    {
        $this->projectService = new ProjectService();
        $this->elasticsearchService = $elasticsearchService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $this->elasticsearchService->logUserActivity('viewed_projects_list');
            $projects = $this->projectService->index();

            $this->elasticsearchService->logMetric('projects_listed', [
                'count' => count($projects),
            ]);

            return response()->json($projects, 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve projects', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProjectRequest $request)
    {
        try {
            $project = $this->projectService->store($request->validated());

            $this->elasticsearchService->logUserActivity('project_created', [
                'project_id' => $project->id,
                'project_name' => $project->name ?? 'unnamed',
            ]);

            $this->elasticsearchService->logMetric('project_created', [
                'project_id' => $project->id,
                'user_id' => auth()->id(),
            ]);

            Log::info('Project created', [
                'project_id' => $project->id,
                'created_by' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Projet créé avec succès',
                'data' => $project
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create project', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $this->elasticsearchService->logUserActivity('viewed_project', ['project_id' => $id]);
            $project = $this->projectService->show($id);
            return response()->json($project, 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve project', ['project_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProjectRequest $request, string $id)
    {
        try {
            $project = $this->projectService->update($request->validated(), $id);

            $this->elasticsearchService->logUserActivity('project_updated', [
                'project_id' => $id,
            ]);

            Log::info('Project updated', ['project_id' => $id]);

            return response()->json([
                'message' => 'Projet mis à jour avec succès',
                'data' => $project
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to update project', ['project_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->elasticsearchService->logUserActivity('project_deleted', ['project_id' => $id]);
            $this->projectService->destroy($id);

            Log::warning('Project deleted', ['project_id' => $id]);

            return response()->json([
                'message' => 'Projet supprimé avec succès'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to delete project', ['project_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Ajouter des utilisateurs à un projet
     */
    public function addUserToProject(ProjectUserRequest $request, string $id)
    {
        try {
            $userIds = $request->validated()['user_ids'];

            $project = $this->projectService->addUserToProject($id, $userIds);

            $this->elasticsearchService->logUserActivity('users_added_to_project', [
                'project_id' => $id,
                'users_count' => count($userIds),
            ]);

            $this->elasticsearchService->logMetric('project_members_added', [
                'project_id' => $id,
                'count' => count($userIds),
            ]);

            Log::info('Users added to project', [
                'project_id' => $id,
                'user_ids' => $userIds,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Utilisateurs ajoutés au projet avec succès',
                'data' => $project
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to add users to project', [
                'project_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Retirer des utilisateurs d'un projet
     */
    public function removeUsers(ProjectUserRequest $request, string $id)
    {
        try {
            $userIds = $request->validated()['user_ids'];

            $project = $this->projectService->removeUserFromProject($id, $userIds);

            $this->elasticsearchService->logUserActivity('users_removed_from_project', [
                'project_id' => $id,
                'users_count' => count($userIds),
            ]);

            $this->elasticsearchService->logMetric('project_members_removed', [
                'project_id' => $id,
                'count' => count($userIds),
            ]);

            Log::info('Users removed from project', [
                'project_id' => $id,
                'user_ids' => $userIds,
            ]);

            return response()->json([
                'message' => 'Utilisateurs retirés avec succès',
                'data' => $project
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to remove users from project', [
                'project_id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Récupérer les membres d'un projet
     */
    public function getMembers(string $id)
    {
        try {
            $this->elasticsearchService->logUserActivity('viewed_project_members', ['project_id' => $id]);
            $members = $this->projectService->getProjectMembers($id);

            $this->elasticsearchService->logMetric('project_members_viewed', [
                'project_id' => $id,
                'members_count' => count($members),
            ]);

            return response()->json($members, 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve project members', [
                'project_id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Récupérer les projets de l'utilisateur connecté
     */
    public function myProjects()
    {
        try {
            $this->elasticsearchService->logUserActivity('viewed_my_projects');
            $projects = $this->projectService->getProjectsForAuthenticatedUser();

            $this->elasticsearchService->logMetric('my_projects_viewed', [
                'user_id' => auth()->id(),
                'count' => count($projects),
            ]);

            return response()->json($projects, 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve user projects', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getProjectBacklogWithStats(string $id)
    {
        try {
            $this->elasticsearchService->logUserActivity('viewed_project_backlog_stats', ['project_id' => $id]);
            $data = $this->projectService->getProjectBacklogWithStats($id);

            Log::info('Project backlog stats viewed', ['project_id' => $id]);

            return response()->json($data, 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve project backlog stats', [
                'project_id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
