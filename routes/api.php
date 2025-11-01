<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\IncrementController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SprintController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserStoryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {
    //AUTH
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    //USER
    Route::apiResource('user', UserController::class);
    Route::get('/users/available', [UserController::class, 'availableUsers'])->middleware('auth:api');
    Route::get('/users/available-for-project/{projectId}', [UserController::class, 'availableUsersForProject'])->middleware('auth:api');

    //PROJECT
    Route::apiResource('project', ProjectController::class)->middleware('auth:api');
    Route::get('/my-project', [ProjectController::class, 'myProjects'])->middleware('auth:api');
    Route::get('/projects/{id}/backlog-stats', [ProjectController::class, 'getProjectBacklogWithStats'])->middleware('auth:api');
    Route::prefix('projects/{id}')->group(function () {
        Route::post('/users', [ProjectController::class, 'addUserToProject'])->middleware('auth:api');
        Route::delete('/users', [ProjectController::class, 'removeUsers'])->middleware('auth:api');
        Route::get('/members', [ProjectController::class, 'getMembers'])->middleware('auth:api');
    });

    //SPRINT
    Route::apiResource('sprint',SprintController::class)->middleware('auth:api');

    //USERSTORY
    Route::apiResource('userStory', UserStoryController::class)->middleware('auth:api');
    Route::get('/backlogs/{backlogId}/user-stories', [UserStoryController::class, 'getBacklogUserStories'])->middleware('auth:api');
    Route::get('/user-stories/progress', [UserStoryController::class, 'getAllProgres'])->middleware('auth:api');
    Route::get('/projects/{projectId}/my-user-stories', [UserStoryController::class, 'getMyProjectUserStories'])->middleware('auth:api');
    Route::get('/projects/{projectId}/user-stories', [UserStoryController::class, 'getProjectUserStories'])->middleware('auth:api');

    //TASK
    Route::apiResource('task', TaskController::class)->middleware('auth:api');
    Route::get('/my-tasks', [TaskController::class, 'myTasks'])->middleware('auth:api');

    //NOTIFICATION
    Route::apiResource('notification', NotificationController::class)->middleware('auth:api');
    Route::get('/my-notification', [NotificationController::class, 'myNotifications'])->middleware('auth:api');
    Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->middleware('auth:api');
    Route::post('/mark-read', [NotificationController::class, 'markAsRead'])->middleware('auth:api');

    //MESSAGE
    Route::apiResource('message', MessageController::class)->middleware('auth:api');
    Route::get('message-by-chat', [MessageController::class, 'messageByChatProject'])->middleware('auth:api');

    //INCREMENT
    Route::apiResource('increment',IncrementController::class)->middleware('auth:api');
    Route::get('increment-by-user-story', [IncrementController::class, 'indexByUserStory']);

    //LOGOUT
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::get('/me', [AuthController::class, 'me'])->middleware('auth:api');
});
