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

    public function test_admin_event_archiving_uses_the_admin_confirmation_modal(): void
    {
        $admin = Admin::create([
            'name' => 'Calendar Admin',
            'email' => 'calendar-admin@example.test',
            'password_hash' => 'unused',
        ]);

        Event::create([
            'title' => 'Campus Fair',
            'event_date' => '2026-10-15',
            'start_time' => '09:00',
            'end_time' => '16:00',
            'created_by' => $admin->admin_id,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.events'))
            ->assertSee('id="admin-action-confirm-modal"', false)
            ->assertSee('data-confirm-title="Archive event?"', false)
            ->assertSee('data-confirm-message="Archive Campus Fair? It will be hidden from the student calendar and can be restored later."', false)
            ->assertSeeText('Archive')
            ->assertDontSeeText('Delete');
    }

    public function test_admin_can_archive_and_restore_an_event_without_deleting_it(): void
    {
        $admin = Admin::create([
            'name' => 'Calendar Admin',
            'email' => 'calendar-admin@example.test',
            'password_hash' => 'unused',
        ]);

        $event = Event::create([
            'title' => 'Campus Fair',
            'event_date' => '2026-10-15',
            'start_time' => '09:00',
            'end_time' => '16:00',
            'created_by' => $admin->admin_id,
        ]);

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.events.archive.store', $event))
            ->assertRedirect(route('admin.events'))
            ->assertSessionHas('success', 'Event archived from the student calendar.');

        $this->assertModelExists($event->fresh());
        $this->assertSame($admin->admin_id, $event->fresh()->archived_by);
        $this->assertNotNull($event->fresh()->archived_at);
        $this->assertDatabaseHas('admin_activity_logs', [
            'action' => 'event_archived',
            'subject_id' => $event->event_id,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.events'))
            ->assertDontSeeText('Campus Fair');
        $this->get(route('admin.events.archive'))
            ->assertSeeText('Campus Fair')
            ->assertSeeText('Restore');
        $this->getJson(route('events.index'))
            ->assertOk()
            ->assertJsonMissing(['title' => 'Campus Fair']);
        $this->get('/?month=2026-10')
            ->assertDontSee('Campus Fair');

        $this->patch(route('admin.events.restore', $event))
            ->assertRedirect(route('admin.events.archive'))
            ->assertSessionHas('success', 'Event restored to the student calendar.');

        $this->assertNull($event->fresh()->archived_at);
        $this->assertNull($event->fresh()->archived_by);
        $this->assertDatabaseHas('admin_activity_logs', [
            'action' => 'event_restored',
            'subject_id' => $event->event_id,
        ]);

        $this->get('/?month=2026-10')
            ->assertSee('Campus Fair');
        $this->getJson(route('events.index'))
            ->assertOk()
            ->assertJsonFragment(['title' => 'Campus Fair']);
    }

    public function test_admin_events_are_ordered_newest_first_and_paginated_in_groups_of_five(): void
    {
        $admin = Admin::create([
            'name' => 'Calendar Admin',
            'email' => 'calendar-admin@example.test',
            'password_hash' => 'unused',
        ]);

        foreach (range(1, 7) as $day) {
            Event::create([
                'title' => "Campus Event {$day}",
                'event_date' => sprintf('2026-10-%02d', $day),
                'start_time' => '09:00',
                'end_time' => '10:00',
                'created_by' => $admin->admin_id,
            ]);
        }

        $firstPage = $this->actingAs($admin, 'admin')
            ->get(route('admin.events'));
        $firstPageContent = $firstPage->getContent();
        $firstPage
            ->assertOk()
            ->assertSee('Campus Event 7')
            ->assertSee('Campus Event 6')
            ->assertSee('Campus Event 5')
            ->assertSee('Campus Event 4')
            ->assertSee('Campus Event 3')
            ->assertDontSee('Campus Event 2')
            ->assertDontSee('Campus Event 1')
            ->assertSee('See more events')
            ->assertSee(route('admin.events', ['page' => 2]), false);

        $this->assertTrue(strpos($firstPageContent, 'Campus Event 7') < strpos($firstPageContent, 'Campus Event 6'));
        $this->assertTrue(strpos($firstPageContent, 'Campus Event 6') < strpos($firstPageContent, 'Campus Event 5'));

        $this->get(route('admin.events', ['page' => 2]))
            ->assertOk()
            ->assertSee('Campus Event 2')
            ->assertSee('Campus Event 1')
            ->assertDontSee('Campus Event 7')
            ->assertDontSee('See more events')
            ->assertSee('Previous events')
            ->assertDontSee(route('admin.events', ['page' => 3]), false);
    }
}
