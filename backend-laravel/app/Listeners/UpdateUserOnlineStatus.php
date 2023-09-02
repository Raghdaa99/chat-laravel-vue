<?php

namespace App\Listeners;

use App\Events\OnlineStatusUpdated;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateUserOnlineStatus
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(OnlineStatusUpdated $event)
    {
        $user = User::find($event->userId);
        if ($user) {
            $user->is_online = $event->isOnline;
            $user->save();
        }
    }
}
