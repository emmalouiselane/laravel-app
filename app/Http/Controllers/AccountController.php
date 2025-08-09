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
        
        return view('account', [
            'user' => $user
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
        $validated = $request->validate([
            'timezone' => ['required', 'string', 'timezone'],
        ]);

        $user = Auth::user();
        $user->timezone = $validated['timezone'];
        $user->save();

        return redirect()
            ->route('account')
            ->with('success', 'Timezone updated successfully!');
    }
}
