<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->latest()
            ->get();

        $user->unreadNotifications->markAsRead();

        $followingIds = $user->following()->pluck('users.id');

        return view('notifications.index', [
            'groupedNotifications' => $this->groupByPeriod($notifications),
            'followingIds' => $followingIds,
        ]);
    }

    /**
     * @param  Collection<int, DatabaseNotification>  $notifications
     * @return array<string, Collection<int, DatabaseNotification>>
     */
    private function groupByPeriod(Collection $notifications): array
    {
        return [
            'today' => $notifications->filter(
                fn (DatabaseNotification $notification): bool => $notification->created_at?->isToday() ?? false
            )->values(),
            'this_month' => $notifications->filter(
                fn (DatabaseNotification $notification): bool => $notification->created_at !== null
                    && ! $notification->created_at->isToday()
                    && $notification->created_at->isCurrentMonth()
            )->values(),
            'earlier' => $notifications->filter(
                fn (DatabaseNotification $notification): bool => $notification->created_at !== null
                    && ! $notification->created_at->isCurrentMonth()
            )->values(),
        ];
    }

    public static function formatTimestamp(?Carbon $date): string
    {
        if ($date === null) {
            return '';
        }

        if ($date->isToday()) {
            $minutes = (int) $date->diffInMinutes();

            if ($minutes < 1) {
                return 'now';
            }

            if ($minutes < 60) {
                return $minutes.'m';
            }

            return (int) $date->diffInHours().'h';
        }

        return $date->format('d M');
    }
}
