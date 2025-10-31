<?php

namespace App\Http\Controllers;

use App\Http\Requests\MessageRequest;
use App\Services\MessageService;
use App\Services\ElasticsearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
    protected $messageService;
    protected $elasticsearchService;

    public function __construct(ElasticsearchService $elasticsearchService)
    {
        $this->messageService = new MessageService();
        $this->elasticsearchService = $elasticsearchService;
    }

    public function index()
    {
        try {
            $this->elasticsearchService->logUserActivity('viewed_messages_list');
            return response()->json($this->messageService->index(), 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve messages', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function store(MessageRequest $request)
    {
        try {
            $message = $this->messageService->store($request->validated());

            $this->elasticsearchService->logUserActivity('message_sent', [
                'message_id' => $message->id,
                'chat_project_id' => $message->chat_project_id,
                'content_length' => strlen($message->content),
            ]);

            $this->elasticsearchService->logMetric('message_sent', [
                'chat_project_id' => $message->chat_project_id,
                'user_id' => auth()->id(),
            ]);

            Log::info('Message sent', [
                'message_id' => $message->id,
                'chat_project_id' => $message->chat_project_id,
            ]);

            return response()->json($message, 201);
        } catch (\Exception $e) {
            Log::error('Failed to send message', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function show(string $id)
    {
        try {
            $this->elasticsearchService->logUserActivity('viewed_message', ['message_id' => $id]);
            return response()->json($this->messageService->show($id), 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve message', ['message_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function update(MessageRequest $request, string $id)
    {
        try {
            $message = $this->messageService->update($request->validated(), $id);

            $this->elasticsearchService->logUserActivity('message_updated', [
                'message_id' => $id,
            ]);

            Log::info('Message updated', ['message_id' => $id]);
            return response()->json($message, 200);
        } catch (\Exception $e) {
            Log::error('Failed to update message', ['message_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->elasticsearchService->logUserActivity('message_deleted', ['message_id' => $id]);
            $this->messageService->destroy($id);
            Log::warning('Message deleted', ['message_id' => $id]);
            return response()->json(['message' => 'Message supprimé'], 200);
        } catch (\Exception $e) {
            Log::error('Failed to delete message', ['message_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function messageByChatProject(Request $request)
    {
        try {
            $chatProjectId = $request->input('chat_project_id')
                ?? $request->get('chat_project_id')
                ?? $request->chat_project_id;

            if (!$chatProjectId) {
                return response()->json([
                    'message' => 'chat_project_id is required',
                    'received_params' => $request->all()
                ], 400);
            }

            $this->elasticsearchService->logUserActivity('viewed_chat_messages', [
                'chat_project_id' => $chatProjectId,
            ]);

            $messages = $this->messageService->getMessagesByChatProject($chatProjectId);

            $this->elasticsearchService->logMetric('chat_messages_viewed', [
                'chat_project_id' => $chatProjectId,
                'messages_count' => count($messages),
            ]);

            return response()->json($messages, 200);

        } catch (\Exception $e) {
            Log::error('Error fetching messages', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Error fetching messages',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
