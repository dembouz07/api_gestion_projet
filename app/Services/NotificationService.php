<?php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationService
{
    public function index()
    {
        return Notification::all();
    }

    public function show(string $id){
        return Notification::findOrFail($id);
    }

    public function store(array $data)
    {
        return Notification::create([
            'user_id' => $data['user_id'],
            'object' => $data['object'],
            'message' => $data['message'],
            'is_read' => false,
        ]);
    }

    public function create(int $userId, string $object, string $message)
    {
        return Notification::create([
            'user_id' => $userId,
            'object' => $object,
            'message' => $message,
            'is_read' => false,
        ]);
    }

    public function update(array $request, string $id){
        $notification = Notification::findOrFail($id);
        $notification->update($request);
        return $notification;
    }

    public function destroy(string $id){
        $notification = Notification::findOrFail($id);
        $notification->delete();
        return response()->json(['message' => 'Notification supprimé avec succès'], 200);
    }

    public function getUserNotificationStats()
    {
        $userId = Auth::id();

        $total = Notification::where('user_id', $userId)->count();
        $unread = Notification::where('user_id', $userId)->where('is_read', false)->count();

        return [
            'total_notifications' => $total,
            'unread_notifications' => $unread,
        ];
    }

    public function markAllAsRead()
    {
        $userId = Auth::id();

        Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['message' => 'Toutes les notifications ont été marquées comme lues'], 200);
    }

    public function markAsRead($id)
    {
        $userId = Auth::id();

        $notification = Notification::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $notification->update(['is_read' => true]);

        return response()->json([
            'message' => 'Notification marquée comme lue'
        ], 200);
    }


    public function getMyNotifications()
    {
        $userId = Auth::id();

        return Notification::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get();
    }
}
