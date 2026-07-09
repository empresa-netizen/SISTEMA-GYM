<?php

namespace App\Http\Controllers;

use App\Models\ClientFeedback;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->get('tab', 'pending');
        $allowedTabs = ['pending', 'viewed', 'resolved', 'all'];
        $tab = in_array($tab, $allowedTabs, true) ? $tab : 'pending';

        $query = ClientFeedback::with('member')
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = $request->string('q')->trim();

                $query->where(function ($query) use ($search) {
                    $query->where('message', 'like', "%{$search}%")
                        ->orWhereHas('member', function ($memberQuery) use ($search) {
                            $memberQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('date'), fn ($query) => $query->whereDate('created_at', $request->date('date')))
            ->when($request->filled('rating'), fn ($query) => $query->where('rating', $request->integer('rating')))
            ->latest();

        if ($tab !== 'all') {
            $query->where('status', $tab);
        }

        $feedbacks = $query->paginate(20)->withQueryString();
        $counts = [
            'pending' => ClientFeedback::where('status', 'pending')->count(),
            'viewed' => ClientFeedback::where('status', 'viewed')->count(),
            'resolved' => ClientFeedback::where('status', 'resolved')->count(),
            'all' => ClientFeedback::count(),
        ];

        return view('prime.feedbacks.index', compact('feedbacks', 'tab', 'counts'));
    }

    public function updateStatus(Request $request, ClientFeedback $feedback)
    {
        abort_unless($feedback->parent_id === parentId(), 403);

        $request->validate(['status' => 'required|in:pending,viewed,resolved']);
        $feedback->update(['status' => $request->status]);

        return back()->with('success', 'Status atualizado.');
    }
}
