<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageDeleted;
use App\Events\MessageSent;
use App\Events\UnreadMessage;
use App\Http\Controllers\Controller;
use App\Jobs\BroadcastUnreadMessageNotification;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Participant;
use App\Models\Recipient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MessagesController extends BaseController
{


    public function index($conversationID)
    {
        $conversation = Conversation::findOrFail($conversationID);

        $user = Auth::user();
        if ($user->isInConversation($conversationID)) {

            $conversation = $conversation->load('participants');

            $messages = Message::select('messages.*')
                ->with('user') // sender
                ->leftJoin('recipients', function ($join) use ($user) {
                    $join->on('messages.id', '=', 'recipients.message_id')
                        ->where('recipients.user_id', '=', $user->id);
                })
                ->where('messages.conversation_id', $conversationID)
                ->where(function ($query) {
                    $query->whereNull('recipients.user_id')
                        ->orWhereNull('recipients.deleted_at');
                })
                ->orderBy('created_at')
                ->get();


//            SELECT messages.* FROM messages
//LEFT JOIN recipients ON messages.id = recipients.message_id
//            AND recipients.user_id = 1
//WHERE messages.conversation_id = 1
//            AND (recipients.user_id IS NULL OR recipients.deleted_at IS NULL);

            return [
                'conversation' => $conversation,
                'messages' => $messages,
            ];

        } else {
            return $this->sendError('Error ', 403);
        }

    }


    public function store(Request $request)
    {

        $request->validate([
            'body' => 'nullable',
            'conversation_id' => [
                Rule::requiredIf(function () use ($request) {
                    return !$request->input('user_id');
                }),
                'nullable',
                'int',
                'exists:conversations,id',
            ],
            'user_id' => 'nullable|integer|exists:users,id',
        ]);

        $user = Auth::user();
        $conversationId = $request->input('conversation_id');
        $userId = $request->input('user_id');

        DB::beginTransaction();
        try {

            $conversation = $this->getOrCreateConversation($conversationId, $userId, $user);

            $message = $this->createMessage($conversation, $user, $request);

            $this->createRecipients($message, $conversation, $user);

            DB::commit();

            $message->load('user');

            broadcast(new MessageSent($message));

            foreach ($conversation->participants as $participant) {
//                if ($participant->id !== $message->user_id) {
                broadcast(new UnreadMessage($participant, $message));
//                }
            }
//            BroadcastUnreadMessageNotification::dispatch($conversation, $message);

        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return [
            'conversation' => $conversation,
            'message' => $message,
            'user' => $user
        ];
    }


    public function destroy(Request $request, Message $message)
    {
        $user = Auth::user();
        $conversation = Conversation::findOrFail($message->conversation_id);
        DB::beginTransaction();
        try {
            if ($user->isInConversation($conversation->id)) {
                if ($message->user_id === $user->id) {
                    // Soft delete the message
                    $message->delete();

                    Recipient::where([
                        'message_id' => $message->id,
                    ])->delete();

                    // Update the conversation's last message if the deleted message was the last one
                    if ($conversation->last_message_id == $message->id) {
                        $conversation->last_message_id = $conversation->messages()
                            ->whereNull('deleted_at')
                            ->orderBy('created_at', 'desc')
                            ->value('id');
                        $conversation->save();
                    }
                    broadcast(new MessageDeleted($message));

                    DB::commit();
                    return [
                        'conversation' => $conversation,
                        'message' => $message,
                    ];
                } else {
                    return [
                        'message' => 'You do not have permission to delete this message',
                    ];
                }

            } else {
                return [
                    'message' => 'You do not have permission to delete this message',
                ];
            }
        } catch
        (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function getOrCreateConversation($conversationId, $userId, $user)
    {
        if ($conversationId) {
            return $user->conversations()->findOrFail($conversationId);
        }

        return $this->createPeerConversation($userId, $user);
    }

    private function createPeerConversation($otherUserId, $user)
    {
        $conversation = Conversation::where('type', '=', 'peer')
            ->whereHas('participants', function ($builder) use ($otherUserId, $user) {
                $builder->join('participants as participants2', 'participants2.conversation_id', '=', 'participants.conversation_id')
                    ->where('participants.user_id', '=', $otherUserId)
                    ->where('participants2.user_id', '=', $user->id);
            })->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'user_id' => $user->id,
                'type' => 'peer',
            ]);

            $conversation->participants()->attach([
                $user->id => ['joined_at' => now()],
                $otherUserId => ['joined_at' => now()],
            ]);
        }

        return $conversation;
    }

    private function createMessage($conversation, $user, $request)
    {
        $type = 'text';
        $body = $request->post('body');
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $body = [
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mimetype' => $file->getMimeType(),
                'file_path' => $file->store('attachments', [
                    'disk' => 'public'
                ]),
            ];
            $type = 'attachment';
        }

        return $conversation->messages()->create([
            'user_id' => $user->id,
            'type' => $type,
            'body' => $body,
        ]);
    }

    private function createRecipients($message, $conversation, $user)
    {
        DB::statement('
        INSERT INTO recipients (user_id, message_id)
        SELECT user_id, ? FROM participants
        WHERE conversation_id = ?', [$message->id, $conversation->id]);

        $conversation->update([
            'last_message_id' => $message->id,
        ]);
    }

}



