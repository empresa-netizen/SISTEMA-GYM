<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InAppNotificationController extends Controller
{
    public function index(): View
    {
        $notifications = auth()->user()
            ->notifications()
            ->latest()
            ->paginate(30);

        return view('prime.notifications.index', compact('notifications'));
    }

    public function markRead(Request $request, string $id): JsonResponse|RedirectResponse
    {
        $notification = auth()->user()->notifications()->where('id', $id)->firstOrFail();
        $notification->markAsRead();

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json(['status' => true]);
        }

        $url = data_get($notification->data, 'url');

        return $url ? redirect()->to($url) : redirect()->route('notifications.inbox');
    }

    public function markAllRead(): RedirectResponse
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Todas as notificações foram marcadas como lidas.');
    }
}
