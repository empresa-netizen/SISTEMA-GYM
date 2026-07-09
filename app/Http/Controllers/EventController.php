<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class EventController extends Controller
{
    /**
     * Display a listing of events
     */
    public function index(Request $request): View
    {
        $parentId = parentId();

        $query = Event::where('parent_id', $parentId);

        // Status filter
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $events = $query->latest('start_time')->paginate(20);
        $upcomingEvents = Event::where('parent_id', $parentId)->upcoming()->take(3)->get();

        return view('events.index', compact('events', 'upcomingEvents'));
    }

    /**
     * Show the form for creating a new event
     */
    public function create(Request $request): View
    {
        $members = Member::where('parent_id', parentId())->orderBy('name')->get(['id', 'name']);

        return view('events.create', [
            'members' => $members,
            'selectedMemberId' => $request->integer('member') ?: null,
        ]);
    }

    /**
     * Store a newly created event
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'location' => 'nullable|string|max:255',
            'max_participants' => 'nullable|integer|min:1',
            'status' => 'required|in:scheduled,ongoing,completed,cancelled',
            'member_id' => 'nullable|exists:members,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $validated['parent_id'] = parentId();
        $validated['registered_count'] = 0;
        if (empty($validated['member_id'])) {
            $validated['member_id'] = null;
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('events', 'public');
        }

        Event::create($validated);

        return redirect()->route('events.index')
            ->with('success', 'Evento criado com sucesso');
    }

    /**
     * Display the specified event
     */
    public function show(Event $event): View
    {
        // Check multi-tenant isolation
        if ($event->parent_id != parentId()) {
            abort(403, 'Unauthorized access');
        }

        return view('events.show', compact('event'));
    }

    /**
     * Show the form for editing the specified event
     */
    public function edit(Event $event): View
    {
        // Check multi-tenant isolation
        if ($event->parent_id != parentId()) {
            abort(403, 'Unauthorized access');
        }

        return view('events.edit', [
            'event' => $event,
            'members' => Member::where('parent_id', parentId())->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Update the specified event
     */
    public function update(Request $request, Event $event): RedirectResponse
    {
        // Check multi-tenant isolation
        if ($event->parent_id != parentId()) {
            abort(403, 'Unauthorized access');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'location' => 'nullable|string|max:255',
            'max_participants' => 'nullable|integer|min:1',
            'status' => 'required|in:scheduled,ongoing,completed,cancelled',
            'member_id' => 'nullable|exists:members,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if (empty($validated['member_id'])) {
            $validated['member_id'] = null;
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($event->image) {
                Storage::disk('public')->delete($event->image);
            }
            $validated['image'] = $request->file('image')->store('events', 'public');
        }

        $event->update($validated);

        return redirect()->route('events.index')
            ->with('success', 'Evento atualizado com sucesso');
    }

    /**
     * Remove the specified event
     */
    public function destroy(Event $event): RedirectResponse
    {
        // Check multi-tenant isolation
        if ($event->parent_id != parentId()) {
            abort(403, 'Unauthorized access');
        }

        // Delete image if exists
        if ($event->image) {
            Storage::disk('public')->delete($event->image);
        }

        $event->delete();

        return redirect()->route('events.index')
            ->with('success', 'Evento excluído com sucesso');
    }

    public function schedule(): View
    {
        return view('events.schedule');
    }

    public function feed()
    {
        $events = Event::where('parent_id', parentId())->get()->map(fn (Event $event) => [
            'id' => $event->id,
            'title' => $event->title,
            'start' => $event->start_time->toIso8601String(),
            'end' => $event->end_time->toIso8601String(),
            'url' => route('events.show', $event),
            'backgroundColor' => match ($event->status) {
                'completed' => '#64748b',
                'cancelled' => '#ef4444',
                'ongoing' => '#22c55e',
                default => '#3b82f6',
            },
        ]);

        return response()->json($events);
    }
}
