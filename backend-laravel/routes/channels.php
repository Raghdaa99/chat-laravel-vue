<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int)$user->id === (int)$id;
});
Broadcast::channel('chat.{id}', function ($user, $id) {
    if ($user->isInConversation($id)){
        return $user;
    }
});



Broadcast::channel('typing.{conversationId}', function ($user, $conversationId) {
    if ($user->isInConversation($conversationId)) {
        return ['id' => $user->id, 'name' => $user->name];
    }
    return false;
});

Broadcast::channel('user-online-status', function($user) {
    return $user;
});

Broadcast::channel('unread-message.{id}', function ($user, $id) {
    if ($user->id == $id) {
        return $user;
    }
});

//Broadcast::channel('online-status.{userId}', function ($user, $userId) {
//    return (int) $user->id === (int) $userId;
//});

//Broadcast::channel('chat-typing.{conversationId}', function ($user, $conversationId) {
//    if ($user->isInConversation($conversationId)) {
//        return ['id' => $user->id, 'name' => $user->name];
//    }
//    return false;
//});
