<?php

namespace App\Http\Controllers;

use App\Models\LostFoundItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class LostFoundItemController extends Controller
{
    public function browse(): View
    {
        return view('lost-found', [
            'items' => $this->approvedItems(),
        ]);
    }

    public function galleryItems(): JsonResponse
    {
        $items = $this->approvedItems();

        return response()->json([
            'html' => view('partials.lost-found-gallery-items', compact('items'))->render(),
            'count' => $items->count(),
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('lost-found', request()->query());
    }

    public function index(): JsonResponse
    {
        return response()->json(LostFoundItem::with(['poster', 'reviewer'])->get());
    }

    public function image(Request $request, LostFoundItem $lostFoundItem): StreamedResponse
    {
        abort_unless($lostFoundItem->approval_status === 'approved' || $request->user('admin'), 404);

        $path = (string) $lostFoundItem->image_path;
        abort_unless(Str::startsWith($path, 'lost-found/'), 404);
        try {
            $stream = Storage::disk($this->lostFoundDisk())->readStream($path);
        } catch (Throwable $exception) {
            report($exception);
            abort(404);
        }

        abort_unless(is_resource($stream), 404);

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $filename = Str::slug($lostFoundItem->item_name).($extension ? ".{$extension}" : '');

        return response()->stream(function () use ($stream): void {
            fpassthru($stream);
            fclose($stream);
        }, 200, [
            'Content-Type' => $this->imageContentType($extension),
            'Cache-Control' => 'no-store, private',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
            'X-Robots-Tag' => 'noindex, nofollow, noarchive',
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        if (! $request->expectsJson()) {
            $validated = $request->validate([
                'full_name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'item_name' => ['required', 'string', 'max:150'],
                'category' => ['required', 'string', 'max:100'],
                'description' => ['required', 'string'],
                'image' => ['sometimes', 'nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'extensions:jpeg,jpg,png,webp', 'max:5120'],
                'status' => ['required', 'in:lost,found'],
                'place' => ['required', 'string', 'max:255'],
            ]);

            $user = User::updateOrCreate(
                ['email' => $validated['email']],
                [
                    'name' => $validated['full_name'],
                ],
            );

            $imagePath = $this->storeUploadedImage($request);

            LostFoundItem::create([
                'posted_by' => $user->id,
                'item_name' => $validated['item_name'],
                'category' => $validated['category'],
                'description' => $validated['description'],
                'image_path' => $imagePath,
                'status' => $validated['status'],
                'approval_status' => 'pending',
                'submitted_at' => now(),
                'place' => $validated['place'],
            ]);

            return redirect()->route('lost-found')->with('success', 'Your item report was submitted for admin approval.');
        }

        $validated = $request->validate([
            'posted_by' => ['required', 'integer', 'exists:users,id'],
            'item_name' => ['required', 'string', 'max:150'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string'],
            'image' => ['sometimes', 'nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'extensions:jpeg,jpg,png,webp', 'max:5120'],
            'status' => ['sometimes', 'in:lost,found,claimed'],
            'place' => ['required', 'string', 'max:255'],
        ]);

        $item = LostFoundItem::create([
            ...$validated,
            'image_path' => $this->storeUploadedImage($request),
            'status' => $validated['status'] ?? 'pending',
            'approval_status' => 'pending',
            'submitted_at' => now(),
        ]);

        return response()->json($item, 201);
    }

    public function show(LostFoundItem $lostFoundItem): JsonResponse
    {
        return response()->json($lostFoundItem->load(['poster', 'reviewer']));
    }

    public function update(Request $request, LostFoundItem $lostFoundItem): JsonResponse
    {
        $validated = $request->validate([
            'item_name' => ['sometimes', 'string', 'max:150'],
            'category' => ['sometimes', 'string', 'max:100'],
            'description' => ['sometimes', 'string'],
            'image' => ['sometimes', 'nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'extensions:jpeg,jpg,png,webp', 'max:5120'],
            'status' => ['sometimes', 'in:lost,found,claimed'],
            'place' => ['sometimes', 'string', 'max:255'],
            'reviewed_by' => ['sometimes', 'nullable', 'integer', 'exists:admins,admin_id'],
        ]);

        unset($validated['image']);

        if (isset($validated['status'])
            && $validated['status'] === 'claimed'
            && ! in_array($lostFoundItem->status, ['found', 'claimed'], true)) {
            return response()->json([
                'message' => 'Only found items can be marked as claimed.',
                'errors' => [
                    'status' => ['Only found items can be marked as claimed.'],
                ],
            ], 422);
        }

        if ($request->hasFile('image')) {
            $imagePath = $this->storeUploadedImage($request);

            if ($lostFoundItem->image_path) {
                Storage::disk($this->lostFoundDisk())->delete($lostFoundItem->image_path);
            }

            $validated['image_path'] = $imagePath;
        }

        // Persist the validated changes before returning the refreshed model.
        $lostFoundItem->update($validated);

        return response()->json($lostFoundItem->fresh(['poster', 'reviewer']));
    }

    public function destroy(LostFoundItem $lostFoundItem): JsonResponse
    {
        $lostFoundItem->delete();

        if ($lostFoundItem->image_path) {
            Storage::disk($this->lostFoundDisk())->delete($lostFoundItem->image_path);
        }

        return response()->json(null, 204);
    }

    private function lostFoundDisk(): string
    {
        return (string) config('filesystems.uploads.lost_found', 'public');
    }

    private function approvedItems(): Collection
    {
        return LostFoundItem::with('poster')
            ->where('approval_status', 'approved')
            ->latest('item_id')
            ->get();
    }

    private function storeUploadedImage(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        try {
            $path = $request->file('image')->store('lost-found', $this->lostFoundDisk());
        } catch (Throwable $exception) {
            report($exception);

            throw ValidationException::withMessages([
                'image' => 'The image could not be uploaded. Please check storage settings and try again.',
            ]);
        }

        if (! is_string($path) || $path === '') {
            throw ValidationException::withMessages([
                'image' => 'The image could not be uploaded. Please check storage settings and try again.',
            ]);
        }

        return $path;
    }

    private function imageContentType(string $extension): string
    {
        return match (strtolower($extension)) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'application/octet-stream',
        };
    }
}
