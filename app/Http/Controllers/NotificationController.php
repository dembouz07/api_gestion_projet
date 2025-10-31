<?php

namespace App\Http\Controllers;

use App\Http\Requests\NotificationRequest;
use App\Services\NotificationService;
use App\Services\ElasticsearchService;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    protected $notificationService;
    protected $elasticsearchService;

    public function __construct(ElasticsearchService $elasticsearchService)
    {
        $this->notificationService = new NotificationService();
        $this->elasticsearchService = $elasticsearchService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $this->elasticsearchService->logUserActivity('viewed_notifications_list');
            $notifications = $this->notificationService->index();

            $this->elasticsearchService->logMetric('notifications_listed', [
                'count' => count($notifications),
            ]);

            return response()->json($notifications, 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve notifications', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(NotificationRequest $request)
    {
        try {
            $notification = $this->notificationService->store($request->validated());

            $this->elasticsearchService->logUserActivity('notification_created', [
                'notification_id' => $notification->id,
                'type' => $notification->type ?? 'general',
            ]);

            $this->elasticsearchService->logMetric('notification_created', [
                'notification_id' => $notification->id,
                'user_id' => auth()->id(),
            ]);

            Log::info('Notification created', [
                'notification_id' => $notification->id,
            ]);

            return response()->json($notification, 201);
        } catch (\Exception $e) {
            Log::error('Failed to create notification', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $this->elasticsearchService->logUserActivity('viewed_notification', ['notification_id' => $id]);
            return response()->json($this->notificationService->show($id), 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve notification', ['notification_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(NotificationRequest $request, string $id)
    {
        try {
            $notification = $this->notificationService->update($request->validated(), $id);

            $this->elasticsearchService->logUserActivity('notification_updated', [
                'notification_id' => $id,
            ]);

            Log::info('Notification updated', ['notification_id' => $id]);
            return response()->json($notification, 200);
        } catch (\Exception $e) {
            Log::error('Failed to update notification', ['notification_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->elasticsearchService->logUserActivity('notification_deleted', ['notification_id' => $id]);
            $this->notificationService->destroy($id);

            Log::warning('Notification deleted', ['notification_id' => $id]);
            return response()->json(['message' => 'Notification supprimée'], 200);
        } catch (\Exception $e) {
            Log::error('Failed to delete notification', ['notification_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Récupérer uniquement les notifications de l'utilisateur connecté
     */
    public function myNotifications()
    {
        try {
            $this->elasticsearchService->logUserActivity('viewed_my_notifications');
            $notifications = $this->notificationService->getMyNotifications();

            $this->elasticsearchService->logMetric('my_notifications_viewed', [
                'user_id' => auth()->id(),
                'count' => count($notifications),
            ]);

            return response()->json($notifications, 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve user notifications', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function markAllAsRead()
    {
        try {
            $this->elasticsearchService->logUserActivity('marked_all_notifications_read');
            $result = $this->notificationService->markAllAsRead();

            Log::info('All notifications marked as read', ['user_id' => auth()->id()]);
            return $result;
        } catch (\Exception $e) {
            Log::error('Failed to mark all notifications as read', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead(string $id)
    {
        try {
            $this->elasticsearchService->logUserActivity('marked_notification_read', ['notification_id' => $id]);
            $result = $this->notificationService->markAsRead($id);

            Log::info('Notification marked as read', ['notification_id' => $id]);
            return $result;
        } catch (\Exception $e) {
            Log::error('Failed to mark notification as read', ['notification_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
}
