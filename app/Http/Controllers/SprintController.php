<?php

namespace App\Http\Controllers;

use App\Http\Requests\SprintRequest;
use App\Models\Sprint;
use App\Services\SprintService;
use App\Services\ElasticsearchService;
use Illuminate\Support\Facades\Log;

class SprintController extends Controller
{
    protected $sprintService;
    protected $elasticsearchService;

    public function __construct(ElasticsearchService $elasticsearchService)
    {
        $this->sprintService = new SprintService();
        $this->elasticsearchService = $elasticsearchService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $this->elasticsearchService->logUserActivity('viewed_sprints_list');

            $sprints = $this->sprintService->index();

            return response()->json($sprints, 200);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve sprints', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            throw $e;
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SprintRequest $request)
    {
        $startTime = microtime(true);

        try {
            $sprint = $this->sprintService->store($request->validated());

            $duration = (microtime(true) - $startTime) * 1000;

            // Log l'activité
            $this->elasticsearchService->logUserActivity('sprint_created', [
                'sprint_id' => $sprint['id'],
                'sprint_number' => $sprint['number'],
                'project_id' => $sprint['project_id'],
                'start_date' => $sprint['start_date'],
                'deadline' => $sprint['deadline'],
            ]);

            // Log la métrique
            $this->elasticsearchService->logMetric('sprint_creation', [
                'sprint_id' => $sprint['id'],
                'project_id' => $sprint['project_id'],
                'duration_days' => $sprint['duration_days'],
            ]);

            // Log la performance
            $this->elasticsearchService->logPerformance('create_sprint', $duration, [
                'sprint_id' => $sprint['id'],
            ]);

            Log::info('Sprint created successfully', [
                'sprint_id' => $sprint['id'],
                'project_id' => $sprint['project_id'],
                'user_id' => auth()->id(),
            ]);

            return response()->json($sprint, 201);

        } catch (\Exception $e) {
            Log::error('Failed to create sprint', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'request_data' => $request->validated(),
            ]);

            $this->elasticsearchService->logMetric('sprint_creation', [
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
            $this->elasticsearchService->logUserActivity('viewed_sprint_details', [
                'sprint_id' => $id,
            ]);

            $sprint = $this->sprintService->show($id);

            return response()->json($sprint, 200);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve sprint', [
                'sprint_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            throw $e;
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SprintRequest $request, string $id)
    {
        $startTime = microtime(true);

        try {
            $sprint = $this->sprintService->update($request->validated(), $id);

            $duration = (microtime(true) - $startTime) * 1000;

            $this->elasticsearchService->logUserActivity('sprint_updated', [
                'sprint_id' => $id,
                'sprint_number' => $sprint['number'],
            ]);

            $this->elasticsearchService->logPerformance('update_sprint', $duration, [
                'sprint_id' => $id,
            ]);

            Log::info('Sprint updated successfully', [
                'sprint_id' => $id,
                'user_id' => auth()->id(),
            ]);

            return response()->json($sprint, 200);

        } catch (\Exception $e) {
            Log::error('Failed to update sprint', [
                'sprint_id' => $id,
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
            $sprint = $this->sprintService->show($id);

            $this->elasticsearchService->logUserActivity('sprint_deleted', [
                'sprint_id' => $id,
                'sprint_number' => $sprint['number'] ?? null,
            ]);

            $this->elasticsearchService->logMetric('sprint_deletion', [
                'sprint_id' => $id,
                'deleted_by' => auth()->id(),
            ]);

            Log::warning('Sprint deleted', [
                'sprint_id' => $id,
                'user_id' => auth()->id(),
            ]);

            $this->sprintService->destroy($id);

            return response()->json(['message' => 'Sprint supprimé avec succès'], 200);

        } catch (\Exception $e) {
            Log::error('Failed to delete sprint', [
                'sprint_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            throw $e;
        }
    }
}
