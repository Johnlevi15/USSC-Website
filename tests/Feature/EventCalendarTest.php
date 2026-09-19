<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Event;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class EventCalendarTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_calendar_renders_only_the_selected_month_events(): void
    {
        $admin = Admin::create([
            'name' => 'Calendar Admin',
            'email' => 'calendar-admin@example.test',
            'password_hash' => 'unused',
        ]);

        Event::create([
            'title' => 'October campus fair',
            'event_date' => '2026-10-15',
            'start_time' => '09:00',
            'end_time' => '16:00',
            'created_by' => $admin->admin_id,
        ]);
        Event::create([
            'title' => 'November assembly',
            'event_date' => '2026-11-01',
            'start_time' => '09:00',
            'end_time' => '10:00',
            'created_by' => $admin->admin_id,
        ]);

        $this->get('/?month=2026-10')
            ->assertSee('October 2026')
            ->assertSee('Events This Month')
            ->assertSee('1 scheduled')
            ->assertSee('October campus fair')
            ->assertDontSee('November assembly');
    }

    public function test_calendar_rejects_an_invalid_month(): void
    {
        $this->get('/?month=not-a-month')
            ->assertRedirect('/')
            ->assertSessionHasErrors('month');
    }
}
