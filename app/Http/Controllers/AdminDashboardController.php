<?php

namespace App\Http\Controllers;

use App\Models\DocumentRequest;
use App\Models\Event;
use App\Models\LostFoundItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function dashboard(): View
    {
        return view('admin.dashboard', [
            'pendingDocuments' => DocumentRequest::where('status', 'pending')->count(),
            'readyDocuments' => DocumentRequest::where('status', 'ready')->count(),
            'unclaimedItems' => LostFoundItem::whereIn('status', ['lost', 'found', 'approved'])->count(),
            'monthlyEvents' => Event::whereBetween('event_date', [now()->startOfMonth(), now()->endOfMonth()])->count(),
            'recentDocuments' => DocumentRequest::with(['user', 'fields'])->latest('request_id')->limit(5)->get(),
            'recentItems' => LostFoundItem::with('poster')->latest('item_id')->limit(5)->get(),
            'upcomingEvents' => Event::whereDate('event_date', '>=', today())
                ->orderBy('event_date')
                ->orderBy('start_time')
                ->limit(5)
                ->get(),
        ]);
    }

    public function documents(): View
    {
        return view('admin.documents', [
            'requests' => DocumentRequest::with(['user', 'fields', 'reviewer'])
                ->latest('request_id')
                ->paginate(15),
        ]);
    }

    public function lostFound(): View
    {
        return view('admin.lost-found', [
            'items' => LostFoundItem::with(['poster', 'reviewer'])
                ->latest('item_id')
                ->paginate(15),
        ]);
    }

    public function events(): View
    {
        return view('admin.events', [
            'events' => Event::with('creator')
                ->orderBy('event_date')
                ->orderBy('start_time')
                ->paginate(15),
        ]);
    }

    public function updateDocument(Request $request, DocumentRequest $documentRequest): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,review,approved,ready,rejected'],
        ]);

        $documentRequest->update([
            'status' => $validated['status'],
            'reviewed_by' => $request->user()->admin->admin_id,
        ]);

        return redirect()->route('admin.documents')->with('success', 'Document request status updated.');
    }

    public function updateLostFound(Request $request, LostFoundItem $lostFoundItem): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:lost,found,approved,claimed,rejected'],
        ]);

        $lostFoundItem->update([
            'status' => $validated['status'],
            'reviewed_by' => $request->user()->admin->admin_id,
        ]);

        return redirect()->route('admin.lost-found')->with('success', 'Lost-and-found item status updated.');
    }

    public function storeEvent(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'event_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ]);

        Event::create([
            ...$validated,
            'created_by' => $request->user()->admin->admin_id,
        ]);

        return redirect()->route('admin.events')->with('success', 'Event published to the student calendar.');
    }
}
