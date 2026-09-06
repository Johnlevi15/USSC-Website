<?php

namespace App\Http\Controllers;

use App\Models\LostFoundItem;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LostFoundItemController extends Controller
{
    public function browse(): View
    {
        return view('lost-found', [
            'items' => LostFoundItem::query()->latest('item_id')->get(),
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
                'status' => ['required', 'in:lost,found'],
                'place' => ['required', 'string', 'max:255'],
            ]);

            $user = User::updateOrCreate(
                ['email' => $validated['email']],
                [
                    'name' => $validated['full_name'],
                ],
            );

            LostFoundItem::create([
                'posted_by' => $user->id,
                'item_name' => $validated['item_name'],
                'category' => $validated['category'],
                'description' => $validated['description'],
                'status' => $validated['status'],
                'place' => $validated['place'],
            ]);

            return redirect()->route('lost-found')->with('success', 'Your item report was submitted successfully.');
        }

        $validated = $request->validate([
            'posted_by' => ['required', 'integer', 'exists:users,id'],
            'item_name' => ['required', 'string', 'max:150'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string'],
            'status' => ['sometimes', 'string', 'max:20'],
            'place' => ['required', 'string', 'max:255'],
        ]);

        $item = LostFoundItem::create([
            ...$validated,
            'status' => $validated['status'] ?? 'pending',
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
            'status' => ['sometimes', 'string', 'max:20'],
            'place' => ['sometimes', 'string', 'max:255'],
            'reviewed_by' => ['sometimes', 'nullable', 'integer', 'exists:admins,admin_id'],
        ]);

        $lostFoundItem->update($validated);

        return response()->json($lostFoundItem->fresh(['poster', 'reviewer']));
    }

    public function destroy(LostFoundItem $lostFoundItem): JsonResponse
    {
        $lostFoundItem->delete();

        return response()->json(null, 204);
    }
}
