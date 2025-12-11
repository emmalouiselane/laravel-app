<?php

namespace App\Http\Controllers;

use App\Models\StickyNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StickyNoteController extends Controller
{
    /**
     * Display the sticky notes board
     */
    public function index()
    {
        $stickyNotes = StickyNote::forUser(Auth::id())
            ->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('sticky-notes.index', [
            'stickyNotes' => $stickyNotes,
        ]);
    }

    /**
     * Store a new sticky note
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string|max:2000',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'position_x' => 'nullable|integer|min:0',
            'position_y' => 'nullable|integer|min:0',
            'width' => 'nullable|integer|min:150|max:500',
            'height' => 'nullable|integer|min:150|max:500',
            'is_pinned' => 'nullable|boolean',
        ]);

        $stickyNote = StickyNote::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'content' => $validated['content'] ?? '',
            'color' => $validated['color'],
            'position_x' => $validated['position_x'] ?? 0,
            'position_y' => $validated['position_y'] ?? 0,
            'width' => $validated['width'] ?? 250,
            'height' => $validated['height'] ?? 250,
            'is_pinned' => $validated['is_pinned'] ?? false,
        ]);

        return response()->json($stickyNote);
    }

    /**
     * Update a sticky note
     */
    public function update(Request $request, StickyNote $stickyNote)
    {
        if ($stickyNote->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'nullable|string|max:2000',
            'color' => 'sometimes|required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'position_x' => 'sometimes|integer|min:0',
            'position_y' => 'sometimes|integer|min:0',
            'width' => 'sometimes|integer|min:150|max:500',
            'height' => 'sometimes|integer|min:150|max:500',
            'is_pinned' => 'sometimes|boolean',
        ]);

        $stickyNote->update($validated);

        return response()->json($stickyNote);
    }

    /**
     * Delete a sticky note
     */
    public function destroy(StickyNote $stickyNote)
    {
        if ($stickyNote->user_id !== Auth::id()) {
            abort(403);
        }

        $stickyNote->delete();

        return response()->json(['message' => 'Sticky note deleted successfully']);
    }

    /**
     * Toggle pin status
     */
    public function togglePin(StickyNote $stickyNote)
    {
        if ($stickyNote->user_id !== Auth::id()) {
            abort(403);
        }

        $stickyNote->update(['is_pinned' => !$stickyNote->is_pinned]);

        return response()->json($stickyNote);
    }
}
