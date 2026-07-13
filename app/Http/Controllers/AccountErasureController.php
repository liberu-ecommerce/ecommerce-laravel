<?php

namespace App\Http\Controllers;

use App\Services\GdprErasureService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AccountErasureController extends Controller
{
    public function __invoke(Request $request, GdprErasureService $service)
    {
        $request->validate(['password' => 'required|string']);

        $user = $request->user();

        // Re-confirm the current password: erasure is irreversible, so guard against
        // an accidental or hijacked-session trigger.
        if (! Hash::check($request->input('password'), $user->password)) {
            throw ValidationException::withMessages([
                'password' => __('The provided password is incorrect.'),
            ]);
        }

        $service->erase($user);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['success' => true, 'message' => 'Your account data has been erased.']);
    }
}
