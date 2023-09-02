<?php

namespace App\Http\Controllers\Api;

use App\Events\OnlineStatusUpdated;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UsersController extends Controller
{
    public function getUsers()
    {
        $user = Auth::user();
        $users = User::where('id', '<>', $user->id)
            ->orderBy('name')
            ->paginate();

        return response()->json($users);
    }

    public function getDataUser($userId)
    {
        $user = new UserResource(User::findorFail($userId));

        return response()->json($user);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:5|confirmed',
        ]);

        $user = Auth::user();

        if (Hash::check($request->current_password, $user->password)) {
            $user->update([
                'password' => Hash::make($request->new_password),
            ]);

            return response()->json(['message' => 'Password changed successfully']);
        }

        return response()->json(['message' => 'Current password is incorrect'], 401);
    }

    public function updateAvatar(Request $request)
    {
        $user = Auth::user();

        if ($request->hasFile('avatar')) {
            if ($user->image) {
                Storage::disk('public')->delete($user->image);
            }

            $avatar = $request->file('avatar');
            $avatarPath = $avatar->store('users', [
                'disk' => 'public'
            ]);


            $user->image = $avatarPath;
            $user->save();

            return response()->json([
                'message' => 'Avatar updated successfully',
                'avatar_url' => $user->avatar_url,
            ]);
        }

        return response()->json(['message' => 'Avatar update failed'], 400);
    }

    public function getNonParticipants(Conversation $conversation)
    {
        $nonParticipants = User::whereNotIn('id', $conversation->participants->pluck('id'))
            ->get();

        return response()->json(['nonParticipants' => $nonParticipants]);
    }

    public function getParticipants(Conversation $conversation)
    {
        $participants = $conversation->participants->sortBy(function ($participant) {
            return $participant->pivot->role === 'admin' ? 0 : 1;
        });

        return response()->json(['participants' => $participants]);
    }



//    public function updateOnlineStatus(Request $request)
//    {
//        $user = Auth::user();
//
//        // Update the user's is_online status based on the request data
//        $user->update(['is_online' => $request->input('is_online', false)]);
//
//        event(new OnlineStatusUpdated($user));
//
//        // Return a response if needed
//        return response()->json(['message' => 'User status updated']);
//    }
//

}
