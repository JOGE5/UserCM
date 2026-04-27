<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'unreadCount' => fn () => Auth::check()
                ? DB::table('chat_users as cu')
                    ->where('cu.user_id', Auth::id())
                    ->where('cu.is_hidden', false)
                    ->join('messages as m', 'm.chat_id', '=', 'cu.chat_id')
                    ->where('m.sender_id', '!=', Auth::id())
                    ->where(function ($q) {
                        $q->whereNull('cu.last_read_at')
                          ->orWhereColumn('m.created_at', '>', 'cu.last_read_at');
                    })
                    ->count()
                : 0,
        ];
    }
}
