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
            'total_revenue' => \App\Models\BusinessProfile::sum('target_revenue'),
            'total_ad_spend' => \App\Models\BusinessProfile::sum('ad_spend'),
            'active_businesses' => \App\Models\BusinessProfile::where('target_revenue', '>', 0)->count(),
        ]);
    }

    public function logs()
    {
        return \App\Models\ActivityLog::with('user:id,name,email,avatar')
            ->latest()
            ->limit(50)
            ->get();
    }

    public function show($id)
    {
        $user = User::with('businessProfile')->findOrFail($id);
        
        // Get last 20 activities for this specific user
        $logs = \App\Models\ActivityLog::where('user_id', $id)
            ->latest()
            ->limit(20)
            ->get();

        return response()->json([
            'user' => $user,
            'logs' => $logs
        ]);
    }

    public function getSettings()
    {
        return \App\Models\SystemSetting::all()->pluck('value', 'key');
    }

    public function updateSetting(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
            'value' => 'nullable'
        ]);

        $setting = \App\Models\SystemSetting::updateOrCreate(
            ['key' => $request->key],
            ['value' => $request->value]
        );

        return response()->json($setting);
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

        // 5. Business Analytics (Scatter: Ad Spend vs Revenue)
        $scatterData = \App\Models\BusinessProfile::select('ad_spend', 'target_revenue', 'business_name')
            ->where('target_revenue', '>', 0)
            ->where('ad_spend', '>', 0)
            ->get()
            ->map(function ($p) {
                return ['x' => (float)$p->ad_spend, 'y' => (float)$p->target_revenue, 'name' => $p->business_name];
            });

        // 6. Pricing Power Distribution
        $prices = \App\Models\BusinessProfile::where('selling_price', '>', 0)->pluck('selling_price');
        $priceBuckets = [
            '< 100k' => $prices->filter(fn($p) => $p < 100000)->count(),
            '100k - 500k' => $prices->filter(fn($p) => $p >= 100000 && $p < 500000)->count(),
            '500k - 1M' => $prices->filter(fn($p) => $p >= 500000 && $p < 1000000)->count(),
            '> 1M' => $prices->filter(fn($p) => $p >= 1000000)->count(),
        ];

        // 7. Conversion Rate Analysis
        $conversions = \App\Models\BusinessProfile::where('conversion_rate', '>', 0)->pluck('conversion_rate');
        $convStats = [
            'avg' => $conversions->avg() ?? 0,
            'max' => $conversions->max() ?? 0,
            'buckets' => [
                'Low (<1%)' => $conversions->filter(fn($c) => $c < 1)->count(),
                'Healthy (1-3%)' => $conversions->filter(fn($c) => $c >= 1 && $c <= 3)->count(),
                'High (>3%)' => $conversions->filter(fn($c) => $c > 3)->count(),
            ]
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
            'business_analytics' => [
                'scatter' => $scatterData,
                'prices' => $priceBuckets,
                'conversion' => $convStats
            ]
        ]);
    }
    public function updateUserPassword(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|min:6',
        ]);

        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->password)
        ]);

        return response()->json(['message' => 'Password updated successfully']);
    }
}
