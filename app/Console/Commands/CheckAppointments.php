<?php

namespace App\Console\Commands;

use App\Events\AppointmentApproaching;
use App\Models\Schedule;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CheckAppointments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-appointments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for upcoming doctor appointments and broadcast events';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // الحصول على الوقت الحالي
        $now = Carbon::now();
        $dayOfWeek = $now->format('l');
        $startTimeWindow = $now->copy()->format('H:i:s');
        $endTimeWindow = $now->copy()->addMinutes(1)->format('H:i:s');

        // -- تسجيل للمساعدة في التشخيص --
        $this->info("Starting appointment check...");
        Log::info('--- CheckAppointments Command Running ---');
        Log::info("Current Time: {$now->toDateTimeString()}");
        Log::info("Searching for Day: {$dayOfWeek}");
        Log::info("Time Window Start (exclusive): > {$startTimeWindow}");
        Log::info("Time Window End (inclusive): <= {$endTimeWindow}");
        // ------------------------------------

        $upcomingSchedules = Schedule::with('doctor')
            ->where('day_of_week', $dayOfWeek)
            ->whereTime('start_time', '>', $startTimeWindow)
            ->whereTime('start_time', '<=', $endTimeWindow)
            ->get();

        if ($upcomingSchedules->isEmpty()) {
            $this->info('No upcoming appointments in the next minute.');
            Log::info('Query found 0 appointments.');
            Log::info('--- CheckAppointments Command Finished ---');
            return;
        }

        $this->info("Found {$upcomingSchedules->count()} upcoming appointments. Broadcasting events...");
        Log::info("Query found {$upcomingSchedules->count()} appointments.");

        foreach ($upcomingSchedules as $schedule) {
            // بث الحدث لكل موعد مقبل
            AppointmentApproaching::dispatch($schedule->doctor, $schedule);
            $this->info("Event dispatched for Dr. {$schedule->doctor->name}");
            Log::info("Event dispatched for Dr. {$schedule->doctor->name} at {$schedule->start_time}");
        }

        $this->info('All events dispatched successfully.');
        Log::info('--- CheckAppointments Command Finished ---');
    }
}

