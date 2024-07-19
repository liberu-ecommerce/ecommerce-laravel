<x-action-section>
    <x-slot name="title">
        {{ __('Connected Accounts') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Connect your social media accounts to enable Sign In with OAuth.') }}
    </x-slot>

    <x-slot name="content">
        <div class="alert-box">
            {{ __('If you feel any of your connected accounts have been compromised, you should disconnect them immediately and change your password.') }}
        </div>

        <div class="account-list">
            @foreach ($this->providers as $provider)
                @php
                    $account = $this->accounts->where('provider', $provider['id'])->first();
                @endphp

                <x-connected-account :provider="$provider" created-at="{{ $account?->created_at }}">
                    <x-slot name="action">
                        @if (!is_null($account))
                            <div class="actions">
                                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos() && !is_null($account->avatar_path))
                                    <button class="btn-link" wire:click="setAvatarAsProfilePhoto({{ $account->id }})">
                                        {{ __('Use Avatar as Profile Photo') }}
                                    </button>
                                @endif

                                @if ($this->accounts->count() > 1 || !is_null(auth()->user()->getAuthPassword()))
                                    <x-danger-button wire:click="confirmRemoveAccount({{ $account->id }})" wire:loading.attr="disabled">
                                        {{ __('Remove') }}
                                    </x-danger-button>
                                @endif
                            </div>
                        @else
                            <x-action-link href="{{ route('oauth.redirect', ['provider' => $provider['id']]) }}">
                                {{ __('Connect') }}
                            </x-action-link>
                        @endif
                    </x-slot>
                </x-connected-account>
            @endforeach
        </div>

        <!-- Logout Other Devices Confirmation Modal -->
        <x-dialog-modal wire:model="confirmingAccountRemoval">
            <x-slot name="title">
                {{ __('Are you sure you want to remove this account?') }}
            </x-slot>

            <x-slot name="content">
                {{ __('Please enter your password to confirm you would like to remove this account.') }}

                <div class="mt-4" x-data="{}" x-on:confirming-delete-user.window="setTimeout(() => $refs.password.focus(), 250)">
                    <x-input type="password" class="mt-1 block w-3/4"
                             autocomplete="current-password"
                             placeholder="{{ __('Password') }}"
                             x-ref="password"
                             wire:model="password"
                             wire:keydown.enter="removeConnectedAccount" />

                    <x-input-error for="password" class="mt-2" />
                </div>
            </x-slot>

            <x-slot name="footer">
                <x-secondary-button wire:click="$toggle('confirmingAccountRemoval')" wire:loading.attr="disabled">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="ml-2" wire:click="removeConnectedAccount" wire:loading.attr="disabled">
                    {{ __('Remove Account') }}
                </x-danger-button>
            </x-slot>
        </x-dialog-modal>
    </x-slot>
</x-action-section>

@push('styles')
<style>
    .alert-box {
        padding: 1rem;
        background-color: rgba(239, 68, 68, 0.1);
        color: #ef4444;
        border-left: 4px solid #dc2626;
        border-radius: 0.5rem;
        font-weight: 500;
        font-size: 0.875rem;
    }
    .account-list {
        margin-top: 1.5rem;
        display: grid;
        gap: 1.5rem;
    }
    .connected-account {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s, box-shadow 0.3s;
    }
    .connected-account:hover {
        transform: translateY(-10px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }
    .actions {
        display: flex;
        align-items: center;
        gap: 1.5rem;
    }
    .btn-link {
        cursor: pointer;
        color: #6b7280;
        font-size: 0.875rem;
        transition: color 0.3s;
    }
    .btn-link:hover {
        color: #374151;
    }
</style>
@endpush

@push('scripts')
<script>
    // Optional JavaScript for handling specific actions on this page
</script>
@endpush
