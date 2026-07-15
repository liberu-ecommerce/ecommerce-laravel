<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    /**
     * Kept in step with LoginResponse. `staff => /app` is gone: no seeder has
     * created that role since cf711cb, and it was a no-op anyway because the
     * fall-through already sent everyone to /app.
     *
     * A brand-new registrant can only be a shopper now — registration grants no
     * team and no role — so in practice this map never matches here. It stays for
     * the case where an existing admin somehow lands on this response, and so the
     * two responses don't drift apart.
     */
    protected array $roleRedirects = [
        'super_admin' => '/admin',
        'admin' => '/admin',
    ];

    /**
     * @param  Request  $request
     * @return RedirectResponse|JsonResponse
     */
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

        if ($user->allTeams()->isNotEmpty()) {
            return redirect()->to('/app');
        }

        // Someone who just signed up to buy something. This defaulted to /app —
        // the team back-office — which they now cannot enter at all.
        return redirect()->intended('/');
    }
}
