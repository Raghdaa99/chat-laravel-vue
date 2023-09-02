<?php

namespace App\Listeners;

use App\Events\TypingEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class TypingListener
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


    public function handle(TypingEvent $event)
    {
        return [
            'user_id' => auth()->id(),
            'typing' => $event->typing,
        ];
    }
}
