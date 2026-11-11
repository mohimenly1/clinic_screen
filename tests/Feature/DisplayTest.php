<?php

use App\Models\Department;
use App\Models\Doctor;
use App\Models\Schedule;
use App\Models\Screen;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DisplayTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->withHeader('X-Inertia-Version', (new \App\Http\Middleware\HandleInertiaRequests)->version(request()) ?? '');

        // Only the display domain is needed here. Account migrations use MySQL-only SQL.
        foreach (glob(database_path('migrations/2025_10_*.php')) as $migration) {
            if (str_contains(basename($migration), 'users')) {
                continue;
            }
            (require $migration)->up();
        }

        Screen::create([
            'name' => 'Test display',
            'screen_code' => 'fast-display',
            'orientation' => 'portrait',
            'resolution' => '2160x3840',
            'is_active' => true,
        ]);
    }

    public function test_display_starts_without_querying_or_sending_inquiry_data(): void
    {
        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = $query->sql;
        });

        $this->get('/display/fast-display', ['X-Inertia' => 'true'])
            ->assertOk()
            ->assertJsonPath('component', 'Display/Show')
            ->assertJsonPath('props.screen.screen_code', 'fast-display')
            ->assertJsonPath('props.mediaItems', [])
            ->assertJsonPath('deferredProps.inquiry', ['departments'])
            ->assertJsonMissingPath('props.departments');

        expect(collect($queries)->filter(fn ($sql) => preg_match('/departments|doctors|schedules/i', $sql)))->toBeEmpty();
    }

    public function test_deferred_inquiry_returns_doctors_without_resending_the_playlist(): void
    {
        $department = Department::create(['name' => 'قسم الاختبار']);
        $doctor = Doctor::create(['name' => 'طبيب الاختبار', 'department_id' => $department->id]);
        Schedule::create([
            'doctor_id' => $doctor->id,
            'day_of_week' => 'Sunday',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'clinic_number' => '12',
            'floor' => '1',
        ]);

        $this->get('/display/fast-display', [
            'X-Inertia' => 'true',
            'X-Inertia-Partial-Component' => 'Display/Show',
            'X-Inertia-Partial-Data' => 'departments',
        ])
            ->assertOk()
            ->assertJsonPath('props.departments.0.name', 'قسم الاختبار')
            ->assertJsonPath('props.departments.0.doctors.0.name', 'طبيب الاختبار')
            ->assertJsonPath('props.departments.0.doctors.0.schedules.0.clinic_number', '12')
            ->assertJsonMissingPath('props.mediaItems');
    }

    public function test_inactive_displays_cannot_load_deferred_data(): void
    {
        Screen::where('screen_code', 'fast-display')->update(['is_active' => false]);

        $this->get('/display/fast-display', [
            'X-Inertia' => 'true',
            'X-Inertia-Partial-Component' => 'Display/Show',
            'X-Inertia-Partial-Data' => 'departments',
        ])->assertNotFound();
    }
}
