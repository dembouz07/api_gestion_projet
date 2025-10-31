<?php

namespace App\Http\Controllers;

use App\Http\Requests\IncrementRequest;
use App\Services\IncrementService;
use App\Services\ElasticsearchService;
use Illuminate\Support\Facades\Log;

class IncrementController extends Controller
{
    protected $incrementService;
    protected $elasticsearchService;

    public function __construct(ElasticsearchService $elasticsearchService)
    {
        $this->incrementService = new IncrementService();
        $this->elasticsearchService = $elasticsearchService;
    }

    public function index()
    {
        try {
            $this->elasticsearchService->logUserActivity('viewed_increments_list');
            return response()->json($this->incrementService->index(), 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve increments', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function store(IncrementRequest $request)
    {
        $startTime = microtime(true);

        try {
            $increment = $this->incrementService->store($request->validated());

            $duration = (microtime(true) - $startTime) * 1000;

            $this->elasticsearchService->logUserActivity('increment_created', [
                'increment_id' => $increment->id,
                'name' => $increment->name,
                'user_story_id' => $increment->user_story_id,
                'has_image' => !empty($increment->image),
                'has_file' => !empty($increment->file),
                'has_link' => !empty($increment->link),
            ]);

            $this->elasticsearchService->logMetric('increment_creation', [
                'increment_id' => $increment->id,
                'user_story_id' => $increment->user_story_id,
                'attachments' => [
                    'image' => !empty($increment->image),
                    'file' => !empty($increment->file),
                    'link' => !empty($increment->link),
                ],
            ]);

            $this->elasticsearchService->logPerformance('create_increment', $duration, [
                'increment_id' => $increment->id,
            ]);

            Log::info('Increment created', [
                'increment_id' => $increment->id,
                'user_story_id' => $increment->user_story_id,
            ]);

            return response()->json($increment, 201);
        } catch (\Exception $e) {
            Log::error('Failed to create increment', ['error' => $e->getMessage()]);

            $this->elasticsearchService->logMetric('increment_creation', [
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function show(string $id)
    {
        try {
            $this->elasticsearchService->logUserActivity('viewed_increment_details', [
                'increment_id' => $id,
            ]);
            return response()->json($this->incrementService->show($id), 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve increment', ['increment_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function update(IncrementRequest $request, string $id)
    {
        $startTime = microtime(true);

        try {
            $increment = $this->incrementService->update($request->validated(), $id);

            $duration = (microtime(true) - $startTime) * 1000;

            $this->elasticsearchService->logUserActivity('increment_updated', [
                'increment_id' => $id,
            ]);

            $this->elasticsearchService->logPerformance('update_increment', $duration, [
                'increment_id' => $id,
            ]);

            Log::info('Increment updated', ['increment_id' => $id]);
            return response()->json($increment, 200);
        } catch (\Exception $e) {
            Log::error('Failed to update increment', ['increment_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function destroy(string $id)
    {
        try {
            $increment = $this->incrementService->show($id);

            $this->elasticsearchService->logUserActivity('increment_deleted', [
                'increment_id' => $id,
                'name' => $increment->name ?? null,
            ]);

            $this->elasticsearchService->logMetric('increment_deletion', [
                'increment_id' => $id,
                'deleted_by' => auth()->id(),
            ]);

            $this->incrementService->destroy($id);
            Log::warning('Increment deleted', ['increment_id' => $id]);
            return response()->json(['message' => 'Increment supprimé'], 200);
        } catch (\Exception $e) {
            Log::error('Failed to delete increment', ['increment_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function indexByUserStory(string $userStoryId)
    {
        try {
            $this->elasticsearchService->logUserActivity('viewed_user_story_increments', [
                'user_story_id' => $userStoryId,
            ]);

            $increments = $this->incrementService->getByUserStory($userStoryId);

            $this->elasticsearchService->logMetric('user_story_increments_count', [
                'user_story_id' => $userStoryId,
                'increments_count' => count($increments),
            ]);

            return response()->json($increments, 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve user story increments', [
                'user_story_id' => $userStoryId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
