<x-guest-layout :title="__('Create an account')">
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <h1 class="heading-3">{{ __('Create an account') }}</h1>
        <p class="mt-2 text-label text-muted">
            {{ __('You only need an email and a password.') }}
        </p>

        <x-validation-errors class="mt-6" />

        <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-5">
            @csrf

            <div>
                <x-label for="name" value="{{ __('Name') }}" />
                <x-input id="name" class="mt-1 block w-full" type="text" name="name"
                         :value="old('name')" required autofocus autocomplete="name" />
                <x-input-error for="name" class="mt-1" />
            </div>

            <div>
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" class="mt-1 block w-full" type="email" name="email"
                         :value="old('email')" required autocomplete="email" />
                <x-input-error for="email" class="mt-1" />
            </div>

            <div>
                <x-label for="password" value="{{ __('Password') }}" />
                <x-input id="password" class="mt-1 block w-full" type="password" name="password"
                         required autocomplete="new-password" />
                <x-input-error for="password" class="mt-1" />
            </div>

            <div>
                <x-label for="password_confirmation" value="{{ __('Confirm password') }}" />
                <x-input id="password_confirmation" class="mt-1 block w-full" type="password"
                         name="password_confirmation" required autocomplete="new-password" />
                <x-input-error for="password_confirmation" class="mt-1" />
            </div>

            @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                <div>
                    <label for="terms" class="flex items-start gap-2">
                        <x-checkbox name="terms" id="terms" required class="mt-0.5" />
                        <span class="text-label text-muted">
                            {!! __('I agree to the :terms_of_service and :privacy_policy', [
                                'terms_of_service' => '<a target="_blank" href="'.route('terms.show').'" class="text-primary-700 underline underline-offset-4 hover:text-primary-600">'.__('Terms of Service').'</a>',
                                'privacy_policy' => '<a target="_blank" href="'.route('policy.show').'" class="text-primary-700 underline underline-offset-4 hover:text-primary-600">'.__('Privacy Policy').'</a>',
                            ]) !!}
                        </span>
                    </label>
                    <x-input-error for="terms" class="mt-1" />
                </div>
            @endif

            <x-button class="w-full justify-center">{{ __('Create account') }}</x-button>
        </form>

        @if (JoelButcher\Socialstream\Socialstream::show())
            <x-socialstream::socialstream />
        @endif

        <p class="mt-6 border-t border-hairline pt-6 text-label text-muted">
            {{ __('Already have an account?') }}
            <a href="{{ route('login') }}"
               class="rounded text-primary-700 underline underline-offset-4 hover:text-primary-600">
                {{ __('Log in') }}
            </a>
        </p>
    </x-authentication-card>
</x-guest-layout>
