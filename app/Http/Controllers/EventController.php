<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class EventController extends Controller
{
    public function calendar(Request $request): View
    {
        $validated = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
        ]);

        $month = isset($validated['month'])
            ? Carbon::createFromFormat('Y-m', $validated['month'])->startOfMonth()
            : now()->startOfMonth();
        $calendarStart = $month->copy()->startOfWeek(Carbon::SUNDAY);
        $calendarEnd = $month->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);
        $events = Schema::hasTable('events')
            ? Event::query()
                ->whereBetween('event_date', [$month->toDateString(), $month->copy()->endOfMonth()->toDateString()])
                ->orderBy('event_date')
                ->orderBy('start_time')
                ->get()
            : collect();
        $eventsByDate = $events->groupBy(fn (Event $event): string => $event->event_date->toDateString());

        return view('welcome', [
            'calendarDays' => collect(CarbonPeriod::create($calendarStart, $calendarEnd)),
            'events' => $events,
            'eventsByDate' => $eventsByDate,
            'month' => $month,
        ]);
    }

    public function index(): JsonResponse
    {
        return response()->json(Event::with(['creator', 'calendars'])->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:5000'],
            'event_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'created_by' => ['required', 'integer', 'exists:admins,admin_id'],
            'calendars' => ['sometimes', 'array'],
            'calendars.*.admin_id' => ['required_with:calendars', 'integer', 'exists:admins,admin_id'],
            'calendars.*.calendar_date' => ['required_with:calendars', 'date'],
            'calendars.*.view_type' => ['required_with:calendars', 'string', 'max:20'],
        ]);

        $event = DB::transaction(function () use ($validated): Event {
            $calendars = $validated['calendars'] ?? [];
            unset($validated['calendars']);

            $event = Event::create($validated);

            foreach ($calendars as $calendar) {
                $event->calendars()->attach($calendar['admin_id'], [
                    'calendar_date' => $calendar['calendar_date'],
                    'view_type' => $calendar['view_type'],
                ]);
            }

            return $event->load(['creator', 'calendars']);
        });

        return response()->json($event, 201);
    }

    public function show(Event $event): JsonResponse
    {
        return response()->json($event->load(['creator', 'calendars']));
    }

    public function update(Request $request, Event $event): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:150'],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'event_date' => ['sometimes', 'date'],
            'start_time' => ['sometimes', 'date_format:H:i'],
            'end_time' => ['sometimes', 'date_format:H:i'],
        ]);

        $event->update($validated);

        return response()->json($event->fresh(['creator', 'calendars']));
    }

    public function destroy(Event $event): JsonResponse
    {
        $event->delete();

        return response()->json(null, 204);
    }
}
