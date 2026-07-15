<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    /**
     * Panels, by role. Checked in order.
     *
     * `staff => /app` used to sit here, but no seeder has created that role since
     * cf711cb, so the entry could never match — and it was a no-op anyway, because
     * the fall-through below already sent everyone to /app.
     */
    protected array $roleRedirects = [
        'super_admin' => '/admin',
        'admin' => '/admin',
    ];

    public function toResponse($request)
    {
        $user = Auth::user();

        setPermissionsTeamId($user->current_team_id);

        if ($request->wantsJson()) {
            return new JsonResponse(['two_factor' => false], 200);
        }

        foreach ($this->roleRedirects as $role => $redirect) {
            if ($user->hasRole($role)) {
                return redirect()->to($redirect);
            }
        }

        // Team members get the back-office they can actually reach.
        if ($user->allTeams()->isNotEmpty()) {
            return redirect()->to('/app');
        }

        // Everyone else is a shopper. This used to default to /app, which sent every
        // customer into the team back-office — and now that /app requires a team,
        // that default would land them on a refusal.
        //
        // intended(), not to(): a shopper logging in was usually part-way through
        // something (a checkout, a product they wanted to save), and that is where
        // they should end up. '/' is only the fallback.
        return redirect()->intended('/');
    }
}
