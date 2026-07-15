{{-- Until now this 500'd, which meant anyone who enabled two-factor auth could not
     log in at all — and the password-reset escape hatch was equally broken.

     The code fields are .data (mono, tabular): a one-time code is read digit by
     digit and transcribed under time pressure, where a 6 that reads as an 8 costs
     the user a whole retry. --}}
<x-guest-layout :title="__('Two-factor authentication')">
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <div x-data="{ recovery: false }">
            <h1 class="heading-3" x-show="! recovery">{{ __('Enter your code') }}</h1>
            <h1 class="heading-3" x-cloak x-show="recovery">{{ __('Use a recovery code') }}</h1>

            <p class="prose-measure mt-2 text-label text-muted" x-show="! recovery">
                {{ __('Open your authenticator app and enter the six-digit code for this account.') }}
            </p>

            <p class="prose-measure mt-2 text-label text-muted" x-cloak x-show="recovery">
                {{ __('Enter one of the recovery codes you saved when you set up two-factor authentication. Each code works once.') }}
            </p>

            <x-validation-errors class="mt-6" />

            <form method="POST" action="{{ route('two-factor.login') }}" class="mt-6 space-y-5">
                @csrf

                <div x-show="! recovery">
                    <x-label for="code" value="{{ __('Authentication code') }}" />
                    <x-input id="code" class="data mt-1 block w-full tracking-widest" type="text"
                             inputmode="numeric" name="code" autofocus x-ref="code"
                             autocomplete="one-time-code" />
                    <x-input-error for="code" class="mt-1" />
                </div>

                <div x-cloak x-show="recovery">
                    <x-label for="recovery_code" value="{{ __('Recovery code') }}" />
                    <x-input id="recovery_code" class="data mt-1 block w-full" type="text"
                             name="recovery_code" x-ref="recovery_code" autocomplete="one-time-code" />
                    <x-input-error for="recovery_code" class="mt-1" />
                </div>

                <x-button class="w-full justify-center">{{ __('Log in') }}</x-button>

                <p class="text-center">
                    <button type="button"
                            class="rounded py-1 text-label text-primary-700 underline underline-offset-4 hover:text-primary-600"
                            x-show="! recovery"
                            x-on:click="recovery = true; $nextTick(() => $refs.recovery_code.focus())">
                        {{ __('I do not have my phone') }}
                    </button>

                    <button type="button"
                            class="rounded py-1 text-label text-primary-700 underline underline-offset-4 hover:text-primary-600"
                            x-cloak
                            x-show="recovery"
                            x-on:click="recovery = false; $nextTick(() => $refs.code.focus())">
                        {{ __('Use an authentication code instead') }}
                    </button>
                </p>
            </form>
        </div>
    </x-authentication-card>
</x-guest-layout>
