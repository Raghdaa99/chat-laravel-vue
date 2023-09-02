<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ConversationsController;
use App\Http\Controllers\Api\MessagesController;
use App\Http\Controllers\Api\ParticipantsController;
use App\Http\Controllers\Api\UsersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->group(function () {
    // for broadcasting
    Broadcast::routes(['middleware' => ['auth:sanctum']]);


    Route::get('conversations', [ConversationsController::class, 'index']);
    Route::get('conversations/{conversation}',[ConversationsController::class,'show']);
    Route::get('conversations/{id}/read', [ConversationsController::class, 'markAsRead']);
    Route::post('create-group-chat', [ConversationsController::class, 'storeGroupChat']);
    Route::get('check-exists-conversation/{userID}',[ConversationsController::class, 'checkExistsConversation']);
    Route::get('check-admin/{conversation}', [ConversationsController::class, 'checkIfAdmin']);
    Route::get('get-images-conversation/{conversation}', [ConversationsController::class, 'getAttachmentConversation']);
    Route::post('/conversation/{conversation}/leave', [ConversationsController::class, 'leaveGroupConversation']);


    Route::post('/conversation/{conversation}/add-participants', [ParticipantsController::class, 'addParticipants']);
    Route::delete('/conversation/{conversation}/participants/{user}', [ParticipantsController::class, 'removeParticipant']);


    Route::get('conversation-messages/{conversationID}', [MessagesController::class, 'index']);
    Route::post('messages', [MessagesController::class, 'store']);
    Route::delete('messages/{message}', [MessagesController::class, 'destroy']);


    Route::get('/users', [UsersController::class, 'getUsers']);
    Route::get('getUser/{userId}', [UsersController::class, 'getDataUser']);
    Route::post('change-password', [UsersController::class, 'changePassword']);
    Route::post('/update-avatar', [UsersController::class, 'updateAvatar']);
    Route::get('conversation/{conversation}/non-participants', [UsersController::class, 'getNonParticipants']);
    Route::get('conversation/{conversation}/participants', [UsersController::class, 'getParticipants']);


    Route::post('logout', [AuthController::class, 'logout']);

});

Route::controller(AuthController::class)->group(function () {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
});

Route::post('forgot-password', [\App\Http\Controllers\Auth\PasswordResetLinkController::class, 'store']);
