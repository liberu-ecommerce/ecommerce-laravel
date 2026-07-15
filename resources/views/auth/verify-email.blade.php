{{-- Note: Features::emailVerification() is NOT enabled in config/fortify.php, so no
     route currently reaches this view. It is kept and fixed rather than deleted
     because enabling that one feature flag is a plausible next step, and it should
     not turn on a page that 500s. --}}
<x-guest-layout :title="__('Verify your email')">
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <h1 class="heading-3">{{ __('Verify your email') }}</h1>
        <p class="prose-measure mt-2 text-label text-muted">
            {{ __('We sent a link to your email address. Click it to confirm your account. If it has not arrived, we can send another.') }}
        </p>

        @if (session('status') == 'verification-link-sent')
            <p class="mt-6 rounded-lg border border-primary-300 bg-primary-50 px-4 py-3 text-label font-medium text-primary-700" role="status">
                {{ __('A new link is on its way to your inbox.') }}
            </p>
        @endif

        <form method="POST" action="{{ route('verification.send') }}" class="mt-6">
            @csrf
            <x-button class="w-full justify-center">{{ __('Send another link') }}</x-button>
        </form>

        <div class="mt-6 flex items-center justify-between gap-4 border-t border-hairline pt-6">
            <a href="{{ route('profile.show') }}"
               class="rounded py-1 text-label text-primary-700 underline underline-offset-4 hover:text-primary-600">
                {{ __('Edit profile') }}
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="rounded py-1 text-label text-muted underline underline-offset-4 hover:text-ink">
                    {{ __('Log out') }}
                </button>
            </form>
        </div>
    </x-authentication-card>
</x-guest-layout>
