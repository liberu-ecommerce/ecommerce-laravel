<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="profile-page">
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            @if (Laravel\Fortify\Features::canUpdateProfileInformation())
                <div class="card">
                    @livewire('profile.update-profile-information-form')
                </div>
                <x-section-border />
            @endif

            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()) && !is_null($user->getAuthPassword()))
                <div class="mt-10 sm:mt-0 card">
                    @livewire('profile.update-password-form')
                </div>
                <x-section-border />
            @else
                <div class="mt-10 sm:mt-0 card">
                    @livewire('profile.set-password-form')
                </div>
                <x-section-border />
            @endif

            @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication() && !is_null($user->getAuthPassword()))
                <div class="mt-10 sm:mt-0 card">
                    @livewire('profile.two-factor-authentication-form')
                </div>
                <x-section-border />
            @endif

            @if (JoelButcher\Socialstream\Socialstream::show())
                <div class="mt-10 sm:mt-0 card">
                    @livewire('profile.connected-accounts-form')
                </div>
            @endif

            @if (!is_null($user->getAuthPassword()))
                <x-section-border />
                <div class="mt-10 sm:mt-0 card">
                    @livewire('profile.logout-other-browser-sessions-form')
                </div>
            @endif

            @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures() && !is_null($user->getAuthPassword()))
                <x-section-border />
                <div class="mt-10 sm:mt-0 card">
                    @livewire('profile.delete-user-form')
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

@push('styles')
<style>
    .profile-page {
        background-color: #f9fafb;
    }
    .card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s, box-shadow 0.3s;
        padding: 1.5rem;
        margin-bottom: 1rem;
    }
    .card:hover {
        transform: translateY(-10px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }
</style>
@endpush

@push('scripts')
<script>
    // Optional JavaScript for handling specific actions on this page
</script>
@endpush
