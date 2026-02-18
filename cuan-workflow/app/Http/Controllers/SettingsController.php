<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\ActivityLog;
use App\Models\User;

class SettingsController extends Controller
{
    // Render the view only. Data will be fetched via API.
    public function index()
    {
        return view('settings');
    }

    public function activityLogs(Request $request)
    {
        $user = $request->user();
        if (!$user) return response()->json([], 401);

        $activities = ActivityLog::where('user_id', $user->id)
                               ->latest()
                               ->take(20)
                               ->get();
        return response()->json($activities);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'whatsapp' => 'required|string|max:20',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user->name = $request->name;
        $user->username = $request->username;
        $user->whatsapp = $request->whatsapp;

        if ($request->hasFile('avatar')) {
            if ($user->avatar && !str_contains($user->avatar, 'googleusercontent.com')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $user->avatar));
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = '/storage/' . $path;
        }

        $user->save();

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'UPDATE_PROFILE',
            'details' => 'User updated profile details',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json(['message' => 'Profile updated successfully!', 'user' => $user]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password does not match.'], 400);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'UPDATE_PASSWORD',
            'details' => 'User changed password',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json(['message' => 'Password changed successfully!']);
    }
    public function systemSettings()
    {
        $settings = \App\Models\SystemSetting::pluck('value', 'key');

        return response()->json([
            'flags' => [
                'calculator' => ($settings['feature_calculator'] ?? '1') === '1',
                'export_pdf' => ($settings['feature_export_pdf'] ?? '1') === '1',
                'registration' => ($settings['feature_registration'] ?? '1') === '1',
            ],
            'broadcast' => [
                'isActive' => ($settings['system_broadcast_active'] ?? '0') === '1',
                'message' => $settings['system_announcement'] ?? '',
            ]
        ]);
    }
}
