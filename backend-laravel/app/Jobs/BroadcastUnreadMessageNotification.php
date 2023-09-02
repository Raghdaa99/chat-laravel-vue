<?php

namespace App\Jobs;

use App\Events\UnreadMessage;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BroadcastUnreadMessageNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $conversation;
    protected $message;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Conversation $conversation, Message $message)
    {
        $this->conversation = $conversation;
        $this->message = $message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->conversation->participants as $participant) {
            if ($participant->id !== $this->message->user_id) {
                broadcast(new UnreadMessage($participant->id, $this->conversation->id));
            }
        }
    }
}
