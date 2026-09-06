<?php

namespace App\Http\Controllers;

use App\Models\LostFoundItem;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class LostFoundItemController extends Controller
{
    public function browse(): View
    {
        return view('lost-found', [
            'items' => LostFoundItem::with('poster')
                ->where('approval_status', 'approved')
                ->latest('item_id')
                ->get(),
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

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        if (! $request->expectsJson()) {
            $validated = $request->validate([
                'full_name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'item_name' => ['required', 'string', 'max:150'],
                'category' => ['required', 'string', 'max:100'],
                'description' => ['required', 'string'],
                'image' => ['sometimes', 'nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
                'status' => ['required', 'in:lost,found'],
                'place' => ['required', 'string', 'max:255'],
            ]);

            $user = User::updateOrCreate(
                ['email' => $validated['email']],
                [
                    'name' => $validated['full_name'],
                ],
            );

            $imagePath = $request->file('image')?->store('lost-found', 'public');

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
            'image' => ['sometimes', 'nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'status' => ['sometimes', 'string', 'max:20'],
            'place' => ['required', 'string', 'max:255'],
        ]);

        $item = LostFoundItem::create([
            ...$validated,
            'image_path' => $request->file('image')?->store('lost-found', 'public'),
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
            'image' => ['sometimes', 'nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'status' => ['sometimes', 'string', 'max:20'],
            'place' => ['sometimes', 'string', 'max:255'],
            'reviewed_by' => ['sometimes', 'nullable', 'integer', 'exists:admins,admin_id'],
        ]);

        unset($validated['image']);

        if ($request->hasFile('image')) {
            if ($lostFoundItem->image_path) {
                Storage::disk('public')->delete($lostFoundItem->image_path);
            }

            $validated['image_path'] = $request->file('image')->store('lost-found', 'public');
        }

        return response()->json($lostFoundItem->fresh(['poster', 'reviewer']));
    }

    public function destroy(LostFoundItem $lostFoundItem): JsonResponse
    {
        $lostFoundItem->delete();

        if ($lostFoundItem->image_path) {
            Storage::disk('public')->delete($lostFoundItem->image_path);
        }

        return response()->json(null, 204);
    }
}
