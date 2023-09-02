<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AttachmentResource;
use App\Http\Resources\ConversationResource;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Participant;
use App\Models\Recipient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ConversationsController extends BaseController
{

    public function index()
    {

        $user = Auth::user();
        return $user->conversations()->with([
            'lastMessage',
            'participants' => function ($builder) use ($user) {
                $builder->where('id', '<>', $user->id);
            },])
            ->withCount([
                'recipients as new_messages' => function ($builder) use ($user) {
                    $builder->where('recipients.user_id', '=', $user->id)
                        ->whereNull('read_at');
                }
            ])
            ->paginate();
    }


    public function show(Conversation $conversation)
    {
        $user = Auth::user();
        if ($user->isInConversation($conversation->id)) {
            return $conversation->load(['messages',
                'participants' => function ($builder) use ($user) {
                    $builder->where('id', '<>', $user->id);
                }]);
        } else {
            $this->sendError('not authorised', 500);
        }

    }

    public function storeGroupChat(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'dec' => 'nullable|string',
            'imagUrl' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'userIds' => 'nullable'
        ]);
        $userIds = json_decode($request->input('userIds'));
        $currentUser = Auth::user();

        try {

            DB::beginTransaction();

            $label = [
                'name' => $request->input('name'),
                'desc' => $request->input('desc'),
                'image' => '',
            ];

            if ($request->hasFile('imagUrl')) {
                $file = $request->file('imagUrl');
                $image = [
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'mimetype' => $file->getMimeType(),
                    'file_path' => $file->store('conversations', [
                        'disk' => 'public'
                    ]),
                ];
                $label['image'] = $image;
            }

            $conversation = Conversation::create([
                'user_id' => $currentUser->id,
                'label' => $label,
                'type' => 'group'
            ]);

            DB::table('participants')->insert([ // currentUser
                'conversation_id' => $conversation->id,
                'user_id' => $currentUser->id,
                'role' => 'admin',
                'joined_at' => Carbon::now(),
            ]);

            if (count($userIds) > 0) {

                $participants = [];
                foreach ($userIds as $userId) {
                    $participants[] = [
                        'conversation_id' => $conversation->id,
                        'user_id' => $userId,
                        'role' => 'member',
                        'joined_at' => Carbon::now(),
                    ];
                }

                DB::table('participants')->insert($participants);
            }
            DB::commit();

            $conversation->load(['lastMessage',
                'participants' => function ($builder) use ($currentUser) {
                    $builder->where('id', '<>', $currentUser->id);
                },])
                ->withCount([
                    'recipients as new_messages' => function ($builder) use ($currentUser) {
                        $builder->where('recipients.user_id', '=', $currentUser->id)
                            ->whereNull('read_at');
                    }
                ]);

            return $this->sendResponse($conversation, 'Success Created Group');

        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

    }

    public function markAsRead($id)
    {
        Recipient::where('user_id', '=', Auth::id())
            ->whereNull('read_at')
            ->whereRaw('message_id IN (
                SELECT id FROM messages WHERE conversation_id = ?
            )', [$id])
            ->update([
                'read_at' => Carbon::now(),
            ]);

        return [
            'message' => 'Messages marked as read',
        ];
    }

    public function checkIfAdmin(Conversation $conversation)
    {
        $isAdmin = auth()->user()->checkIfAdmin($conversation->id);

        return response()->json(['is_admin' => $isAdmin]);
    }

    public function checkExistsConversation($otherUserID)
    {

        $currentUser = Auth::user();
        $conversation = Conversation::where('type', 'peer')
            ->whereHas('participants', function ($query) use ($currentUser, $otherUserID) {
                $query->join('participants as participants2', 'participants2.conversation_id', '=', 'participants.conversation_id')
                    ->where('participants.user_id', '=', $otherUserID)
                    ->where('participants2.user_id', '=', $currentUser->id);
            })
//            ->has('participants', '=', 2)
            ->with(['lastMessage', 'participants' => function ($builder) use ($currentUser) {
                $builder->where('user_id', '<>', $currentUser->id);
            }])
            ->first();

        return response()->json(['conversation' => $conversation]);
    }

    public function getAttachmentConversation(Conversation $conversation)
    {
        $messagesWithAttachments = Message::where('conversation_id', $conversation->id)
            ->where('type', 'attachment')
            ->get();

        $attachmentImages = [];
        $attachmentFiles = [];

        foreach ($messagesWithAttachments as $message) {
            if (Str::match('/image\/.+/', $message->body['mimetype'])) {
                $attachmentImages[] = new AttachmentResource($message['body']);
            } else {
                $attachmentFiles[] = new AttachmentResource($message['body']);
            }
        }

        return [
            'attachmentImages' => $attachmentImages,
            'attachmentFiles' => $attachmentFiles
        ];
    }

    public function leaveGroupConversation(Conversation $conversation)
    {
        $authUser = Auth::user();


        if ($conversation->type !== 'group') {
            return response()->json(['message' => 'You can only leave group conversations.'], 400);
        }


        $conversation->participants()->detach($authUser->id);

        return response()->json(['message' => 'You have left the group conversation.']);
    }

}


