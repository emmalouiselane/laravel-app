<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            // Debug: Log the Google user data
            Log::info('Google User Raw Data:', $googleUser->user);
            
            $user = User::updateOrCreate(
                ['google_id' => $googleUser->getId()],
                [
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'avatar' => $googleUser->getAvatar(),
                    'email_verified_at' => now(),
                ]
            );
            
            // Only set default timezone if it's a new user or timezone is not set
            if (is_null($user->timezone)) {
                $user->timezone = 'Europe/London';
                $user->save();
            }

            // Log in the user
            Auth::login($user, true); // Remember the user
            
            // Debug: Check if user is authenticated
            if (Auth::check()) {
                Log::info('User authenticated successfully', ['user_id' => $user->id]);
            } else {
                Log::error('User authentication failed');
            }

            // Set a session variable to verify session is working
            session(['google_auth_complete' => true]);
            
            // Redirect to intended URL or home
            return redirect()->intended('/dashboard');
            
        } catch (\Exception $e) {
            Log::error('Google login error: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect('/')
                ->with('error', 'Failed to login with Google. Please try again.');
        }
    }
}
