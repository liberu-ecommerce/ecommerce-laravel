{{-- The authenticated account shell (profile, teams, API tokens). Like
     guest-layout, this component was referenced but never existed, so
     /user/profile, /user/api-tokens and the team pages were all a hard 500.

     There is a grim symmetry worth recording: two-factor auth is enabled in
     Fortify and its challenge page was crashing, but the only page that can
     turn 2FA on is this one — which was also crashing. The lockout was
     unreachable purely because the door to it was broken too.

     Unlike guest-layout this keeps the storefront chrome: account pages are a
     place you visit while shopping, not a flow you are trying to escape. --}}
@props(['header' => null])

@extends('layouts.app')

@section('content')
    @isset($header)
        <header class="border-b border-hairline bg-surface">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
    @endisset

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        {{ $slot }}
    </div>
@endsection
