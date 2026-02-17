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
use App\Models\ActivityLog;

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

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'REGISTER',
            'details' => 'User registered via Email',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

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

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to return to the user. Finally, we'll return the proper response.
        $status = \Illuminate\Support\Facades\Password::sendResetLink(
            $request->only('email')
        );

        return $status === \Illuminate\Support\Facades\Password::RESET_LINK_SENT
                    ? response()->json(['message' => __($status)])
                    : response()->json(['message' => __($status)], 400);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = \Illuminate\Support\Facades\Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new \Illuminate\Auth\Events\PasswordReset($user));
            }
        );

        return $status === \Illuminate\Support\Facades\Password::PASSWORD_RESET
                    ? response()->json(['message' => __($status)])
                    : response()->json(['message' => __($status)], 400);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user) {
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'LOGOUT',
                'details' => 'User logged out',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            $user->currentAccessToken()->delete();
        }
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
                // Update Google ID
                $updateData = [
                    'google_id' => $googleUser->id,
                    'auth_provider' => 'google',
                    'last_login_at' => now(),
                ];

                // Only overwrite avatar if it's not a local upload
                if (!$user->avatar || !str_starts_with($user->avatar, '/storage/')) {
                    $updateData['avatar'] = $googleUser->avatar;
                }

                $user->update($updateData);
            } else {
                // Create new user
                $name = $googleUser->name;
                
                // Generate unique username
                $baseUsername = strtolower(str_replace(' ', '', $name));
                $username = $baseUsername . rand(100, 999);
                
                // Ensure uniqueness
                while (User::where('username', $username)->exists()) {
                    $username = $baseUsername . rand(1000, 9999);
                }
                
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

                ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => 'REGISTER',
                    'details' => 'User registered via Google',
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }

            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'LOGIN',
                'details' => 'User logged in via Google',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

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
