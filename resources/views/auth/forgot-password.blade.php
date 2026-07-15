<x-guest-layout :title="__('Reset your password')">
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <h1 class="heading-3">{{ __('Reset your password') }}</h1>
        <p class="prose-measure mt-2 text-label text-muted">
            {{ __('Tell us the email on your account and we will send a link to set a new password.') }}
        </p>

        @session('status')
            {{-- Moss, not a second green: the affirmative colour is the brand. --}}
            <p class="mt-6 rounded-lg border border-primary-300 bg-primary-50 px-4 py-3 text-label font-medium text-primary-700" role="status">
                {{ $value }}
            </p>
        @endsession

        <x-validation-errors class="mt-6" />

        <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-5">
            @csrf

            <div>
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" class="mt-1 block w-full" type="email" name="email"
                         :value="old('email')" required autofocus autocomplete="username" />
                <x-input-error for="email" class="mt-1" />
            </div>

            <x-button class="w-full justify-center">{{ __('Email me a reset link') }}</x-button>
        </form>

        <p class="mt-6 border-t border-hairline pt-6 text-label text-muted">
            <a href="{{ route('login') }}"
               class="rounded text-primary-700 underline underline-offset-4 hover:text-primary-600">
                {{ __('Back to log in') }}
            </a>
        </p>
    </x-authentication-card>
</x-guest-layout>
