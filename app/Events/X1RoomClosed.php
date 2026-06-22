<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use App\Jobs\ProcessX1Result;
use App\Models\X1RoomInstance;

class X1RoomClosed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public X1RoomInstance $room;

    public function __construct(X1RoomInstance $room)
    {
        $this->room = $room;
        // Dispatch processing job for closed room (lightweight scaffold)
        ProcessX1Result::dispatch($room);
    }
}
