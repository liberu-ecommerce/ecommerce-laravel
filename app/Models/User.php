<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasDefaultTenant;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasDefaultTenant, HasTenants
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto {
        HasProfilePhoto::profilePhotoUrl as getPhotoUrl;
    }

    // use HasConnectedAccounts;
    use HasRoles;
    use HasTeams;
    use Notifiable;

    // use SetsProfilePhotoFromUrl;
    use TwoFactorAuthenticatable;

    public function canAccessPanel(Panel $panel): bool
    {
        $user = auth()->user();
        if ($panel->getId() === 'admin' && ! $user->hasRole('super_admin')) {
            return false;
        }

        return true; // TODO: Check panel and role
    }

    public function wishlist()
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
        ];
    }

    /**
     * Get the URL to the user's profile photo.
     */
    public function profilePhotoUrl(): Attribute
    {
        return filter_var($this->profile_photo_path, FILTER_VALIDATE_URL)
            ? Attribute::get(fn () => $this->profile_photo_path)
            : $this->getPhotoUrl();
    }

    /**
     * @return array<Model> | Collection
     */
    public function getTenants(Panel $panel): array|Collection
    {
        // Every team the user can act in — owned + membership. Using only
        // ownedTeams (as before) hid shared/member teams from the tenant switcher.
        return $this->allTeams();
    }

    public function canAccessTenant(Model $tenant): bool
    {
        // belongsToTeam covers ownership AND membership; the previous
        // teams-only check locked a team's own owner out of their tenant.
        return $this->belongsToTeam($tenant);
    }

    public function canAccessFilament(): bool
    {
        //        return $this->hasVerifiedEmail();
        return true;
    }

    public function getDefaultTenant(Panel $panel): ?Model
    {
        return $this->latestTeam;
    }

    public function latestTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'current_team_id');
    }

    public function browsingHistory(): HasMany
    {
        return $this->hasMany(BrowsingHistory::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    public function membership(): BelongsToMany
    {
        return $this->belongsToMany(Team::class)->withPivot(['role']);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }

    public function customerSegments(): BelongsToMany
    {
        return $this->belongsToMany(CustomerSegment::class, 'customer_segment_members', 'user_id', 'segment_id')
            ->withPivot('added_at')
            ->withTimestamps();
    }

    public function customerMetric(): HasOne
    {
        return $this->hasOne(CustomerMetric::class);
    }

    public function giftRegistries(): HasMany
    {
        return $this->hasMany(GiftRegistry::class);
    }

    public function productInteractions(): HasMany
    {
        return $this->hasMany(ProductInteraction::class);
    }
}
