<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AccountController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        $user = Auth::user();
        
        if (!$user) {
            abort(403, 'Unauthorized access');
        }
        
        // Log the user data being passed to the view
        // \Log::info('Loading account page', [
        //     'user_id' => $user->id,
        //     'timezone' => $user->timezone,
        //     'timezone_from_db' => \DB::table('users')->where('id', $user->id)->value('timezone')
        // ]);
        
        return view('account', [
            'user' => $user->fresh() // Always get fresh data from the database
        ]);
    }

    /**
     * Update the user's timezone.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateTimezone(Request $request)
    {
        // \Log::info('Timezone update request received', ['request' => $request->all()]);
        
        $validated = $request->validate([
            'timezone' => ['required', 'string', 'timezone'],
        ]);

        try {
            // Start a database transaction
            \DB::beginTransaction();

            $user = Auth::user();
            // \Log::info('Current user timezone before update', [
            //     'user_id' => $user->id,
            //     'current_timezone' => $user->timezone
            // ]);
            
            // Update and save
            $user->timezone = $validated['timezone'];
            $saved = $user->save();
            
            // Explicitly commit the transaction
            \DB::commit();
            
            // Refresh the user model from the database
            $user->refresh();
            
            // Update the authenticated user's session data
            Auth::setUser($user);
            
            // \Log::info('User save result', [
            //     'user_id' => $user->id,
            //     'saved' => $saved, 
            //     'new_timezone' => $user->timezone,
            //     'fresh_check' => $user->fresh()->timezone // Check database directly
            // ]);

            if ($saved) {
                // Clear the form old input to prevent flashing old data
                $request->session()->forget('_old_input');
                
                return redirect()
                    ->route('account')
                    ->with('success', 'Timezone updated successfully!');
            }

            return back()
                ->with('error', 'Failed to update timezone. Please try again.');
                
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error updating timezone', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()
                ->with('error', 'An error occurred while updating timezone: ' . $e->getMessage());
        }
    }
}
