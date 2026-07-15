{{-- The page reached from the emailed reset link — and, until now, a hard 500,
     because <x-guest-layout> did not exist. This is the escape hatch from every
     other lockout, so it is the last page that should crash. --}}
<x-guest-layout :title="__('Choose a new password')">
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <h1 class="heading-3">{{ __('Choose a new password') }}</h1>
        <p class="mt-2 text-label text-muted">
            {{ __('Pick something you have not used on this account before.') }}
        </p>

        <x-validation-errors class="mt-6" />

        <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-5">
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div>
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" class="mt-1 block w-full" type="email" name="email"
                         :value="old('email', $request->email)" required autofocus autocomplete="username" />
                <x-input-error for="email" class="mt-1" />
            </div>

            <div>
                <x-label for="password" value="{{ __('New password') }}" />
                <x-input id="password" class="mt-1 block w-full" type="password" name="password"
                         required autocomplete="new-password" />
                <x-input-error for="password" class="mt-1" />
            </div>

            <div>
                <x-label for="password_confirmation" value="{{ __('Confirm new password') }}" />
                <x-input id="password_confirmation" class="mt-1 block w-full" type="password"
                         name="password_confirmation" required autocomplete="new-password" />
                <x-input-error for="password_confirmation" class="mt-1" />
            </div>

            <x-button class="w-full justify-center">{{ __('Save new password') }}</x-button>
        </form>
    </x-authentication-card>
</x-guest-layout>
