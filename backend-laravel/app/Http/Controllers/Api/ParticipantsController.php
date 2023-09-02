<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\Request;

class ParticipantsController extends Controller
{

    public function addParticipants(Request $request, Conversation $conversation)
    {
        $userIdsToAdd = $request->input('user_ids');

        // Add new participants to the conversation
        $conversation->participants()->attach($userIdsToAdd);

        return response()->json(['message' => 'Participants added successfully']);
    }

    public function removeParticipant(Conversation $conversation, User $user)
    {


        $isAdmin = auth()->user()->checkIfAdmin($conversation->id);

        if (!$isAdmin) {
            return response()->json(['message' => 'You do not have permission to remove participants.'], 403);
        }

        $conversation->participants()->detach($user->id);

        return response()->json(['message' => 'Participant removed successfully']);
    }
}
