<?php

namespace App\Http\Controllers;

use App\Mail\DocumentRequestStatusUpdated;
use App\Models\Admin;
use App\Models\AdminActivityLog;
use App\Models\DocumentRequest;
use App\Models\EmailNotification;
use App\Models\Event;
use App\Models\LostFoundItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

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
            'recentDocuments' => DocumentRequest::with(['user', 'fields', 'documentType'])->latest('request_id')->limit(5)->get(),
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
            'requests' => DocumentRequest::with(['user', 'fields', 'reviewer', 'documentType'])
                ->latest('request_id')
                ->paginate(15),
        ]);
    }

    public function reviewDocument(DocumentRequest $documentRequest): View
    {
        $documentRequest->load([
            'user',
            'fields',
            'reviewer',
            'documentType.fields',
        ]);

        return view('admin.document-review', [
            'documentRequest' => $documentRequest,
        ]);
    }

    public function viewDocumentAttachment(DocumentRequest $documentRequest, string $fieldName): StreamedResponse
    {
        $documentRequest->loadMissing('documentType.fields');

        $field = $documentRequest->fields()
            ->where('field_name', $fieldName)
            ->firstOrFail();
        $definition = $documentRequest->documentType?->fields->firstWhere('field_name', $fieldName);

        abort_unless($definition && in_array($definition->field_type, ['file', 'image'], true), 404);

        $path = (string) $field->field_value;
        abort_unless(Str::startsWith($path, "document-uploads/{$documentRequest->request_id}/"), 404);
        $disk = $this->existingDocumentUploadDisk($path);
        abort_unless(Storage::disk($disk)->exists($path), 404);

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $filename = Str::slug($definition->field_label ?: $fieldName).($extension ? ".{$extension}" : '');

        return Storage::disk($disk)->response($path, $filename, [
            'Cache-Control' => 'no-store, private',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
            'X-Robots-Tag' => 'noindex, nofollow, noarchive',
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

    public function reviewLostFound(LostFoundItem $lostFoundItem): View
    {
        $lostFoundItem->load(['poster', 'reviewer']);

        return view('admin.lost-found-review', [
            'item' => $lostFoundItem,
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
            'logs' => AdminActivityLog::with('admin')->latest()->paginate(25),
        ]);
    }

    public function updateDocument(Request $request, DocumentRequest $documentRequest): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,review,approved,ready,rejected'],
            'admin_remarks' => ['nullable', 'string', 'max:2000', 'required_if:status,rejected'],
            'notify_student' => ['sometimes', 'boolean'],
        ], [
            'admin_remarks.required_if' => 'Please provide a rejection reason before marking this request as rejected.',
        ]);

        $adminId = $this->adminId($request);

        $documentRequest->update([
            'status' => $validated['status'],
            'reviewed_by' => $adminId,
            'admin_remarks' => filled($validated['admin_remarks'] ?? null)
                ? trim($validated['admin_remarks'])
                : null,
        ]);

        $statusLabels = [
            'pending' => 'Pending',
            'review' => 'Under Review',
            'approved' => 'Approved',
            'ready' => 'Ready for Pickup',
            'rejected' => 'Rejected',
        ];

        $statusLabel = $statusLabels[$validated['status']] ?? $validated['status'];

        $this->record(
            $request,
            'document_reviewed',
            "Updated document request #{$documentRequest->request_id} to {$statusLabel}.",
            $documentRequest,
        );

        $message = 'Document review saved.';

        if ($request->boolean('notify_student')) {
            $documentRequest->load(['user', 'documentType']);
            $recipientEmail = $documentRequest->user?->email;

            if (blank($recipientEmail)) {
                EmailNotification::create([
                    'request_id' => $documentRequest->request_id,
                    'user_id' => $documentRequest->user_id,
                    'sent_by' => $adminId,
                    'status' => 'failed',
                    'sent_at' => null,
                ]);

                $message .= ' The review was saved, but the student has no email address to notify.';
            } else {
                try {
                    Mail::to($recipientEmail)
                        ->send(new DocumentRequestStatusUpdated($documentRequest));

                    EmailNotification::create([
                        'request_id' => $documentRequest->request_id,
                        'user_id' => $documentRequest->user_id,
                        'sent_by' => $adminId,
                        'status' => 'sent',
                        'sent_at' => now(),
                    ]);

                    $this->record(
                        $request,
                        'document_notification_sent',
                        "Sent a status update for document request #{$documentRequest->request_id} to {$recipientEmail}.",
                        $documentRequest,
                    );

                    $message .= ' Student notification sent.';
                } catch (Throwable $exception) {
                    report($exception);

                    EmailNotification::create([
                        'request_id' => $documentRequest->request_id,
                        'user_id' => $documentRequest->user_id,
                        'sent_by' => $adminId,
                        'status' => 'failed',
                        'sent_at' => null,
                    ]);

                    $message .= ' The review was saved, but the email notification could not be sent.';
                }
            }
        }

        return redirect()->route('admin.documents.review', $documentRequest)->with('success', $message);
    }

    public function updateLostFound(Request $request, LostFoundItem $lostFoundItem): RedirectResponse
    {
        $validated = $request->validate([
            'approval_status' => ['required', 'in:approved,rejected,pending'],
            'status' => ['required', 'in:lost,found,claimed'],
        ]);

        if ($validated['status'] === 'claimed' && ! in_array($lostFoundItem->status, ['found', 'claimed'], true)) {
            throw ValidationException::withMessages([
                'status' => 'Only found items can be marked as claimed.',
            ]);
        }

        $lostFoundItem->update([
            'approval_status' => $validated['approval_status'],
            'status' => $validated['status'],
            'reviewed_by' => $this->adminId($request),
        ]);

        $this->record($request, 'lost_found_reviewed', "Updated item #{$lostFoundItem->item_id}: approval {$validated['approval_status']}, state {$validated['status']}.", $lostFoundItem);

        return redirect()->route('admin.lost-found.review', $lostFoundItem)->with('success', 'Lost-and-found item status updated.');
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
            'created_by' => $this->adminId($request),
        ]);

        $this->record($request, 'event_created', "Published event: {$event->title}.", $event);

        return redirect()->route('admin.events')->with('success', 'Event published to the student calendar.');
    }

    public function editEvent(Event $event): View
    {
        $event->load('creator');

        return view('admin.event-edit', [
            'event' => $event,
        ]);
    }

    public function updateEvent(Request $request, Event $event): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:5000'],
            'event_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ]);

        $event->update($validated);

        $this->record($request, 'event_updated', "Updated event: {$event->title}.", $event);

        return redirect()->route('admin.events')->with('success', 'Event updated successfully.');
    }

    public function destroyEvent(Request $request, Event $event): RedirectResponse
    {
        $eventTitle = $event->title;

        // Record the action before deleting so the event still exists when the log is created.
        $this->record($request, 'event_deleted', "Deleted event: {$eventTitle}.", $event);

        $event->delete();

        return redirect()->route('admin.events')->with('success', 'Event deleted from the student calendar.');
    }

    private function record(Request $request, string $action, string $description, Model $subject): void
    {
        AdminActivityLog::create([
            'admin_id' => $this->adminId($request),
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject::class,
            'subject_id' => $subject->getKey(),
        ]);
    }

    private function existingDocumentUploadDisk(string $path): string
    {
        $configuredDisk = (string) config('filesystems.uploads.documents', 'local');

        foreach (array_unique([$configuredDisk, 'local', 'public']) as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                return $disk;
            }
        }

        return $configuredDisk;
    }

    private function adminId(Request $request): int
    {
        $admin = $request->user('admin');

        abort_unless($admin instanceof Admin, 403);

        return $admin->admin_id;
    }
}
