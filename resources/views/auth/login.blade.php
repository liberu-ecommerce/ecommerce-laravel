{{-- The "Demo Credentials" panel that used to sit here is deliberately gone. It
     rendered unconditionally — in production — advertising admin@example.com and
     staff@example.com as valid usernames next to the word "password". The seeder
     randomises that password, so the panel was simultaneously useless to an honest
     visitor and a free username list for a dishonest one.

     The old copy also read "Please sign in to access the admin panel", on the
     storefront login that shoppers use. --}}
<x-guest-layout :title="__('Log in')">
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <h1 class="heading-3">{{ __('Log in') }}</h1>
        <p class="mt-2 text-label text-muted">
            {{ __('Sign in to track orders, save a wishlist, and check out faster.') }}
        </p>

        <x-validation-errors class="mt-6" />

        @session('status')
            <p class="mt-6 rounded-lg border border-primary-300 bg-primary-50 px-4 py-3 text-label font-medium text-primary-700" role="status">
                {{ $value }}
            </p>
        @endsession

        <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5">
            @csrf

            <div>
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" class="mt-1 block w-full" type="email" name="email"
                         :value="old('email')" required autofocus autocomplete="username" />
                <x-input-error for="email" class="mt-1" />
            </div>

            <div>
                <x-label for="password" value="{{ __('Password') }}" />
                <x-input id="password" class="mt-1 block w-full" type="password" name="password"
                         required autocomplete="current-password" />
                <x-input-error for="password" class="mt-1" />
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3">
                <label for="remember_me" class="flex items-center gap-2 py-1">
                    <x-checkbox id="remember_me" name="remember" />
                    <span class="text-label text-muted">{{ __('Remember me') }}</span>
                </label>

                <a href="{{ route('password.request') }}"
                   class="rounded py-1 text-label text-primary-700 underline underline-offset-4 hover:text-primary-600">
                    {{ __('Forgot your password?') }}
                </a>
            </div>

            <x-button class="w-full justify-center">{{ __('Log in') }}</x-button>
        </form>

        @if (JoelButcher\Socialstream\Socialstream::show())
            <x-socialstream::socialstream />
        @endif

        <p class="mt-6 border-t border-hairline pt-6 text-label text-muted">
            {{ __('New here?') }}
            <a href="{{ route('register') }}"
               class="rounded text-primary-700 underline underline-offset-4 hover:text-primary-600">
                {{ __('Create an account') }}
            </a>
        </p>
    </x-authentication-card>
</x-guest-layout>
