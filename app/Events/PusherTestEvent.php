<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PusherTestEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $data;

    public function __construct()
    {
        $this->data = ['message' => 'Hello from Laravel! The connection is working.'];
    }

    public function broadcastOn(): array
    {
        return [new Channel('displays')];
    }

    public function broadcastAs(): string
    {
        return 'PusherTestEvent';
    }
}