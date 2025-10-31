<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Services\UserService;
use App\Services\ElasticsearchService;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    protected $userService;
    protected $elasticsearchService;

    public function __construct(ElasticsearchService $elasticsearchService)
    {
        $this->userService = new UserService();
        $this->elasticsearchService = $elasticsearchService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $this->elasticsearchService->logUserActivity('viewed_users_list');

            $users = $this->userService->index();

            $this->elasticsearchService->logMetric('users_list_viewed', [
                'users_count' => count($users),
            ]);

            return response()->json($users, 200);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve users', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
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
            $this->elasticsearchService->logUserActivity('viewed_user_details', [
                'viewed_user_id' => $id,
            ]);

            $user = $this->userService->show($id);

            return response()->json($user, 200);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve user', [
                'user_id' => $id,
                'error' => $e->getMessage(),
                'requested_by' => auth()->id(),
            ]);
            throw $e;
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserRequest $request, string $id)
    {
        $startTime = microtime(true);

        try {
            $oldUser = $this->userService->show($id);
            $user = $this->userService->update($request->validated(), $id);

            $duration = (microtime(true) - $startTime) * 1000;

            // Déterminer les champs modifiés
            $changes = [];
            foreach ($request->validated() as $field => $value) {
                if ($field !== 'password' && isset($oldUser->$field) && $oldUser->$field != $value) {
                    $changes[$field] = [
                        'old' => $oldUser->$field,
                        'new' => $value,
                    ];
                }
            }

            $this->elasticsearchService->logUserActivity('user_updated', [
                'updated_user_id' => $id,
                'changes' => $changes,
            ]);

            $this->elasticsearchService->logMetric('user_update', [
                'user_id' => $id,
                'fields_changed' => array_keys($changes),
            ]);

            $this->elasticsearchService->logPerformance('update_user', $duration, [
                'user_id' => $id,
            ]);

            Log::info('User updated successfully', [
                'user_id' => $id,
                'updated_by' => auth()->id(),
                'changes' => $changes,
            ]);

            return response()->json($user, 200);

        } catch (\Exception $e) {
            Log::error('Failed to update user', [
                'user_id' => $id,
                'error' => $e->getMessage(),
                'updated_by' => auth()->id(),
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
            $user = $this->userService->show($id);

            $this->elasticsearchService->logUserActivity('user_deleted', [
                'deleted_user_id' => $id,
                'deleted_user_email' => $user->email ?? null,
            ]);

            $this->elasticsearchService->logMetric('user_deletion', [
                'user_id' => $id,
                'deleted_by' => auth()->id(),
            ]);

            Log::warning('User deleted', [
                'user_id' => $id,
                'deleted_by' => auth()->id(),
            ]);

            $this->userService->destroy($id);

            return response()->json(['message' => 'Utilisateur supprimé avec succès'], 200);

        } catch (\Exception $e) {
            Log::error('Failed to delete user', [
                'user_id' => $id,
                'error' => $e->getMessage(),
                'requested_by' => auth()->id(),
            ]);
            throw $e;
        }
    }

    /**
     * Récupère les utilisateurs qui ne sont pas encore membres d'un projet spécifique
     */
    public function availableUsersForProject($projectId)
    {
        try {
            $this->elasticsearchService->logUserActivity('viewed_available_users_for_project', [
                'project_id' => $projectId,
            ]);

            $users = $this->userService->getAvailableUsersForProject($projectId);

            $this->elasticsearchService->logMetric('available_users_query', [
                'project_id' => $projectId,
                'available_count' => count($users),
            ]);

            return response()->json($users, 200);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve available users for project', [
                'project_id' => $projectId,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            throw $e;
        }
    }

    /**
     * Récupère les utilisateurs disponibles (sans projet et non PM)
     */
    public function availableUsers()
    {
        try {
            $this->elasticsearchService->logUserActivity('viewed_available_users');

            $users = $this->userService->getAvailableUsers();

            $this->elasticsearchService->logMetric('available_users_query', [
                'available_count' => count($users),
            ]);

            return response()->json($users, 200);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve available users', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            throw $e;
        }
    }
}
