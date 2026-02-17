<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'username' => 'required|unique:users',
            'whatsapp' => 'required',
        ]);

        $agent = $this->parseUserAgent($request->header('User-Agent'));
        Log::info('Register User-Agent: ' . $request->header('User-Agent'));
        Log::info('Parsed Agent: ', $agent);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'username' => $request->username,
            'whatsapp' => $request->whatsapp,
            'is_premium' => true,
            'device_type' => $agent['device'],
            'browser' => $agent['browser'],
        ]);

        // Create default business profile
        $user->businessProfile()->create();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Credensial yang diberikan salah.'],
            ]);
        }
        
        if ($user->is_banned) {
             throw ValidationException::withMessages([
                'email' => ['Akun Anda ditangguhkan. Hubungi admin.'],
            ]);
        }

        $agent = $this->parseUserAgent($request->header('User-Agent'));
        Log::info('Login User-Agent: ' . $request->header('User-Agent'));
        Log::info('Parsed Agent: ', $agent);

        $user->update([
            'last_login_at' => now(),
            'device_type' => $agent['device'],
            'browser' => $agent['browser'],
        ]);
        
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    private function parseUserAgent($userAgent)
    {
        $browser = 'Unknown';
        $device = 'Desktop';

        if (preg_match('/Mobile|Android|iPhone|iPad|iPod/', $userAgent)) {
            $device = 'Mobile';
        } elseif (preg_match('/Tablet|iPad/', $userAgent)) {
            $device = 'Tablet';
        }

        if (preg_match('/Chrome/', $userAgent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Safari/', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/Firefox/', $userAgent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/Edge/', $userAgent)) {
            $browser = 'Edge';
        }

        return ['device' => $device, 'browser' => $browser];
    }

    // Google Auth
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            // Find user by Google ID or Email
            $user = User::where('google_id', $googleUser->id)
                        ->orWhere('email', $googleUser->email)
                        ->first();

            if ($user) {
                // Update Google ID and Avatar if changed
                $user->update([
                    'google_id' => $googleUser->id,
                    'avatar' => $googleUser->avatar,
                    'auth_provider' => 'google',
                    'last_login_at' => now(),
                ]);
            } else {
                // Create new user
                $name = $googleUser->name;
                $username = strtolower(str_replace(' ', '', $name)) . rand(100, 999);
                
                $user = User::create([
                    'name' => $name,
                    'email' => $googleUser->email,
                    'username' => $username,
                    'password' => null, // Password defined as nullable in migration
                    'google_id' => $googleUser->id,
                    'avatar' => $googleUser->avatar,
                    'auth_provider' => 'google',
                    'role' => 'user',
                    'is_premium' => true, // Default premium for now as per current logic
                    'last_login_at' => now(),
                    'whatsapp' => '-', // Placeholder as whatsapp is required
                ]);

                // Create default business profile
                $user->businessProfile()->create();
            }

            // Generate Token
            $token = $user->createToken('auth_token')->plainTextToken;

            // Redirect to frontend with token
            return redirect('/?auth_token=' . $token . '&user_name=' . urlencode($user->name));

        } catch (\Exception $e) {
            Log::error('Google Login Error: ' . $e->getMessage());
            return redirect('/login?error=' . urlencode('Google Login Failed'));
        }
    }
}
