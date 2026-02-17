<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function users()
    {
        return User::latest()->paginate(20);
    }

    public function stats()
    {
        return response()->json([
            'total_users' => User::count(),
            'new_users_today' => User::whereDate('created_at', today())->count(),
            'active_users_today' => User::whereDate('last_login_at', today())->count(),
        ]);
    }

    public function ban(User $user)
    {
        $user->update(['is_banned' => true]);
        // Ideally revoke tokens too
        $user->tokens()->delete();
        return response()->json(['message' => 'User banned']);
    }

    public function unban(User $user)
    {
        $user->update(['is_banned' => false]);
        return response()->json(['message' => 'User unbanned']);
    }

    public function promote(User $user)
    {
        $user->update(['role' => 'admin']);
        return response()->json(['message' => 'User promoted to admin']);
    }

    public function charts()
    {
        // 1. User Growth (Last 30 Days)
        $growthData = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // Fill missing dates
        $growth = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $record = $growthData->firstWhere('date', $date);
            $growth[] = [
                'date' => $date,
                'count' => $record ? $record->count : 0
            ];
        }

        // 2. User Segments (stub for now, assuming all "Standard")
        $segments = [
            'Standard' => User::where('role', 'user')->count(),
            'Premium' => User::where('is_premium', true)->count(),
            'Admin' => User::where('role', 'admin')->count(),
        ];

        return response()->json([
            'user_growth' => $growth,
            'user_segments' => $segments,
            'device_stats' => User::selectRaw('device_type, count(*) as count')
                ->whereNotNull('device_type')
                ->groupBy('device_type')
                ->pluck('count', 'device_type'),
            'browser_stats' => User::selectRaw('browser, count(*) as count')
                ->whereNotNull('browser')
                ->groupBy('browser')
                ->pluck('count', 'browser'),
        ]);
    }
}
