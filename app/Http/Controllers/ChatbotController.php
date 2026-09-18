<?php

namespace App\Http\Controllers;

use App\Models\DocumentType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChatbotController extends Controller
{
    public function reply(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:500'],
        ]);

        $message = Str::lower(trim($validated['message']));
        $documentTypes = DocumentType::query()
            ->active()
            ->with('fields')
            ->orderBy('name')
            ->get();

        if ($this->containsAny($message, ['hello', 'hi', 'hey', 'good morning', 'good afternoon', 'good evening'])) {
            return $this->response(
                'Hello! I am the USSC Assistant. I can help with document requests, requirements, tracking, Lost & Found, events, and contact information.',
                ['How do I request a document?', 'How do I track my request?', 'Lost and Found']
            );
        }

        foreach ($documentTypes as $type) {
            $keywords = collect(preg_split('/\s+/', Str::lower($type->name)))
                ->filter(fn ($word) => strlen($word) >= 4 && ! in_array($word, ['document', 'request', 'form']));

            $matchesType = $keywords->contains(fn ($word) => str_contains($message, $word));

            if ($matchesType && $this->containsAny($message, ['requirement', 'requirements', 'need', 'required', 'fields', 'information', 'apply', 'request'])) {
                $requiredFields = $type->fields
                    ->where('is_required', true)
                    ->pluck('field_label')
                    ->values();

                $requirements = $requiredFields->isNotEmpty()
                    ? $requiredFields->implode(', ')
                    : 'No additional requirements are currently listed.';

                $description = $type->description ? $type->description."\n\n" : '';

                return $this->response(
                    $type->name."\n\n".$description."Required information: {$requirements}\n\nGo to Request Document in the navigation menu to submit your request.",
                    ['How do I request a document?', 'How do I track my request?']
                );
            }
        }

        if ($this->containsAny($message, ['available document', 'documents available', 'document types', 'what documents', 'list of documents'])) {
            if ($documentTypes->isEmpty()) {
                return $this->response('There are currently no active document types available.');
            }

            $names = $documentTypes->pluck('name')->map(fn ($name) => '• '.$name)->implode("\n");

            return $this->response(
                "The following document types are currently available:\n\n{$names}\n\nYou can submit one through the Request Document page.",
                ['How do I request a document?', 'What are the requirements?']
            );
        }

        if ($this->containsAny($message, ['request document', 'request a document', 'apply for document', 'get a document', 'submit document', 'how to request'])) {
            return $this->response(
                "To request a document:\n\n1. Open Request Document.\n2. Select the document type.\n3. Complete the required information.\n4. Submit the request.\n5. Save the tracking number shown after submission.\n\nNo student account or login is required.",
                ['What documents are available?', 'How do I track my request?']
            );
        }

        if ($this->containsAny($message, ['requirement', 'requirements', 'what do i need', 'required information'])) {
            if ($documentTypes->isEmpty()) {
                return $this->response('There are currently no active document types available.');
            }

            $reply = "Document requirements depend on the document type.\n\n";

            foreach ($documentTypes as $type) {
                $fields = $type->fields->where('is_required', true)->pluck('field_label')->implode(', ');
                $reply .= "• {$type->name}: ".($fields ?: 'No additional requirements listed.')."\n";
            }

            $reply .= "\nSelect a document on the Request Document page to see its complete form.";

            return $this->response($reply, ['How do I request a document?', 'What documents are available?']);
        }

        if ($this->containsAny($message, ['track', 'tracking', 'status', 'where is my request', 'check request'])) {
            return $this->response(
                "To track a document request:\n\n1. Open Track Request.\n2. Enter the tracking number issued after submission.\n3. Click Track.\n\nA tracking number looks like USSC-2026-0001. No login is required.",
                ['What does pending mean?', 'How do I request a document?']
            );
        }

        $statusReplies = [
            'pending' => 'Pending means your request was submitted and is waiting for review by the USSC administration.',
            'review' => 'Under Review means an administrator is currently checking your request.',
            'approved' => 'Approved means your request has been reviewed and accepted.',
            'ready' => 'Ready means your requested document is ready for the next step or release. Follow any instructions provided by the USSC office.',
            'rejected' => 'Rejected means the request was not approved. Please contact the USSC office if you need clarification.',
        ];

        foreach ($statusReplies as $status => $reply) {
            if (str_contains($message, $status)) {
                return $this->response($reply);
            }
        }

        if ($this->containsAny($message, ['lost', 'found', 'missing item', 'lost item', 'report item'])) {
            return $this->response(
                "For Lost & Found:\n\n• Open Lost & Found to browse approved reports.\n• Use Report Item if you lost or found something.\n• Complete the requested item details accurately.\n\nNo student login is required.",
                ['How do I request a document?', 'Events']
            );
        }

        if ($this->containsAny($message, ['event', 'events', 'calendar', 'activity', 'activities'])) {
            return $this->response('USSC events are shown on the Home page calendar. Open Home to view scheduled activities and event information.');
        }

        if ($this->containsAny($message, ['contact', 'email', 'phone', 'telephone', 'office', 'help desk'])) {
            return $this->response("You may contact the USSC through:\n\nEmail: clsuussc@clsu2.edu.ph\nTelephone: (044) 940 8785\n\nYou may also visit the USSC office at Central Luzon State University.");
        }

        if ($this->containsAny($message, ['thank you', 'thanks', 'thank'])) {
            return $this->response('You’re welcome! Let me know if you need help with another USSC service.');
        }

        return $this->response(
            "I couldn't find an exact answer to that question yet.\n\nI can currently help with:\n• Document requests\n• Document requirements\n• Request tracking\n• Lost & Found\n• Events\n• USSC contact information",
            ['How do I request a document?', 'How do I track my request?', 'Lost and Found']
        );
    }

    private function containsAny(string $message, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (str_contains($message, $keyword)) {
                return true;
            }
        }

        return false;
    }

    private function response(string $reply, array $suggestions = []): JsonResponse
    {
        return response()->json([
            'reply' => $reply,
            'suggestions' => $suggestions,
        ]);
    }
}
