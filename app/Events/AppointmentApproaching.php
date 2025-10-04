<?php

namespace App\Events;

use App\Models\Doctor;
use App\Models\Schedule;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class AppointmentApproaching implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Doctor $doctor;
    public Schedule $schedule;
    public ?string $photoUrl;

    public function __construct(Doctor $doctor, Schedule $schedule)
    {
        $this->doctor = $doctor;
        $this->schedule = $schedule;
        $this->photoUrl = $doctor->photo_path ? Storage::url($doctor->photo_path) : null;
    }

    public function broadcastOn(): array
    {
        return [new Channel('displays')];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'AppointmentApproaching';
    }
}

