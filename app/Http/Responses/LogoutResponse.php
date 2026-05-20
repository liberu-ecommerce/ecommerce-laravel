<?php
 
namespace App\Http\Responses;
 
use Illuminate\Http\RedirectResponse;
 
class LogoutResponse implements \Filament\Auth\Http\Responses\Contracts\LogoutResponse
{
    public function toResponse($request): RedirectResponse
    {
        return redirect('/login');
    }
}