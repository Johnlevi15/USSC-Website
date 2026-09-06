<?php

namespace App\Http\Controllers;

use App\Models\AdminActivityLog;
use App\Models\DocumentRequest;
use App\Models\Event;
use App\Models\LostFoundItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function dashboard(): View
    {
        return view('admin.dashboard', [
            'pendingDocuments' => DocumentRequest::where('status', 'pending')->count(),
            'readyDocuments' => DocumentRequest::where('status', 'ready')->count(),
            'unclaimedItems' => LostFoundItem::where('approval_status', 'approved')
                ->whereIn('status', ['lost', 'found'])
                ->count(),
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
            'pendingItems' => LostFoundItem::with(['poster', 'reviewer'])
                ->where('approval_status', 'pending')
                ->latest('item_id')
                ->get(),
            'processedItems' => LostFoundItem::with(['poster', 'reviewer'])
                ->whereIn('approval_status', ['approved', 'rejected'])
                ->latest('item_id')
                ->get(),
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

    public function logs(): View
    {
        return view('admin.logs', [
            'logs' => AdminActivityLog::with('admin.user')->latest()->paginate(25),
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

        $this->record($request, 'document_reviewed', "Updated document request #{$documentRequest->request_id} to {$validated['status']}.", $documentRequest);

        return redirect()->route('admin.documents')->with('success', 'Document request status updated.');
    }

    public function updateLostFound(Request $request, LostFoundItem $lostFoundItem): RedirectResponse
    {
        $validated = $request->validate([
            'approval_status' => ['required', 'in:approved,rejected,pending'],
            'status' => ['required', 'in:lost,found,claimed'],
        ]);

        if ($validated['status'] === 'claimed' && $lostFoundItem->status !== 'found') {
            throw ValidationException::withMessages([
                'status' => 'Only found items can be marked as claimed.',
            ]);
        }

        $lostFoundItem->update([
            'approval_status' => $validated['approval_status'],
            'status' => $validated['status'],
            'reviewed_by' => $request->user()->admin->admin_id,
        ]);

        $this->record($request, 'lost_found_reviewed', "Updated item #{$lostFoundItem->item_id}: approval {$validated['approval_status']}, state {$validated['status']}.", $lostFoundItem);

        return redirect()->route('admin.lost-found')->with('success', 'Lost-and-found item status updated.');
    }

    public function storeEvent(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:5000'],
            'event_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ]);

        $event = Event::create([
            ...$validated,
            'created_by' => $request->user()->admin->admin_id,
        ]);

        $this->record($request, 'event_created', "Published event: {$event->title}.", $event);

        return redirect()->route('admin.events')->with('success', 'Event published to the student calendar.');
    }

    private function record(Request $request, string $action, string $description, Model $subject): void
    {
        AdminActivityLog::create([
            'admin_id' => $request->user()->admin->admin_id,
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject::class,
            'subject_id' => $subject->getKey(),
        ]);
    }
}
