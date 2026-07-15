<x-guest-layout :title="__('Confirm your password')">
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <h1 class="heading-3">{{ __('Confirm your password') }}</h1>
        <p class="prose-measure mt-2 text-label text-muted">
            {{ __('You are about to change something sensitive. Enter your password to continue.') }}
        </p>

        <x-validation-errors class="mt-6" />

        <form method="POST" action="{{ route('password.confirm') }}" class="mt-6 space-y-5">
            @csrf

            <div>
                <x-label for="password" value="{{ __('Password') }}" />
                <x-input id="password" class="mt-1 block w-full" type="password" name="password"
                         required autocomplete="current-password" autofocus />
                <x-input-error for="password" class="mt-1" />
            </div>

            <x-button class="w-full justify-center">{{ __('Confirm') }}</x-button>
        </form>
    </x-authentication-card>
</x-guest-layout>
