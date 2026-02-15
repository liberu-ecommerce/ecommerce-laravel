<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'tax_class_id',
        'country',
        'state',
        'city',
        'zip_code',
        'rate',
        'name',
        'priority',
        'compound',
        'shipping',
        'is_active',
    ];

    protected $casts = [
        'rate' => 'decimal:4',
        'priority' => 'integer',
        'compound' => 'boolean',
        'shipping' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function taxClass(): BelongsTo
    {
        return $this->belongsTo(TaxClass::class);
    }

    /**
     * Calculate tax amount for a given price
     */
    public function calculateTax(float $price): float
    {
        return round($price * ($this->rate / 100), 2);
    }

    /**
     * Get matching tax rates for a location
     */
    public static function findMatchingRates(
        string $country,
        ?string $state = null,
        ?string $city = null,
        ?string $zipCode = null,
        ?int $taxClassId = null
    ): \Illuminate\Database\Eloquent\Collection {
        $query = static::where('is_active', true)
            ->where('country', $country);

        if ($taxClassId) {
            $query->where('tax_class_id', $taxClassId);
        }

        // Match specific location first, then fall back to broader areas
        $rates = collect();
        
        // Try exact match
        if ($zipCode || $city || $state) {
            $exactMatch = (clone $query);
            if ($zipCode) $exactMatch->where('zip_code', $zipCode);
            if ($city) $exactMatch->where('city', $city);
            if ($state) $exactMatch->where('state', $state);
            $rates = $exactMatch->orderBy('priority', 'desc')->get();
        }

        // Fall back to state level if no exact match
        if ($rates->isEmpty() && $state) {
            $rates = (clone $query)
                ->where('state', $state)
                ->whereNull('city')
                ->whereNull('zip_code')
                ->orderBy('priority', 'desc')
                ->get();
        }

        // Fall back to country level
        if ($rates->isEmpty()) {
            $rates = (clone $query)
                ->whereNull('state')
                ->whereNull('city')
                ->whereNull('zip_code')
                ->orderBy('priority', 'desc')
                ->get();
        }

        return $rates;
    }
}
