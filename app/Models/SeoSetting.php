<?php

namespace App\Models;

use App\Traits\IsTenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SeoSetting extends Model
{
    use HasFactory;
    use IsTenantModel;

    protected $fillable = [
        'seoable_type',
        'seoable_id',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_title',
        'og_description',
        'og_image',
        'og_type',
        'twitter_card',
        'twitter_title',
        'twitter_description',
        'twitter_image',
        'canonical_url',
        'robots',
        'structured_data',
        'focus_keyword',
        'seo_score',
    ];

    protected $casts = [
        'meta_keywords' => 'array',
        'structured_data' => 'array',
        'seo_score' => 'integer',
    ];

    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getMetaTitleAttribute($value): string
    {
        return $value ?: $this->generateDefaultTitle();
    }

    public function getMetaDescriptionAttribute($value): string
    {
        return $value ?: $this->generateDefaultDescription();
    }

    protected function generateDefaultTitle(): string
    {
        if ($this->seoable_type === Product::class) {
            return $this->seoable->name;
        }

        if ($this->seoable_type === ProductCollection::class) {
            return $this->seoable->title;
        }

        if ($this->seoable_type === Page::class) {
            return $this->seoable->title;
        }

        return config('app.name');
    }

    protected function generateDefaultDescription(): string
    {
        if ($this->seoable_type === Product::class) {
            return $this->seoable->short_description ?: 
                   substr(strip_tags($this->seoable->description), 0, 160);
        }

        if ($this->seoable_type === ProductCollection::class) {
            return $this->seoable->description ?: 
                   "Shop our {$this->seoable->title} collection";
        }

        if ($this->seoable_type === Page::class) {
            return substr(strip_tags($this->seoable->content), 0, 160);
        }

        return 'Quality products at great prices';
    }

    public function generateStructuredData(): array
    {
        if ($this->seoable_type === Product::class) {
            return $this->generateProductStructuredData();
        }

        return [];
    }

    protected function generateProductStructuredData(): array
    {
        $product = $this->seoable;

        return [
            '@context' => 'https://schema.org/',
            '@type' => 'Product',
            'name' => $product->name,
            'description' => $product->description,
            'image' => $product->image_url,
            'sku' => $product->sku ?? $product->id,
            'offers' => [
                '@type' => 'Offer',
                'price' => $product->price,
                'priceCurrency' => 'USD',
                'availability' => $product->inventory_count > 0 ? 
                    'https://schema.org/InStock' : 
                    'https://schema.org/OutOfStock',
                'url' => route('products.show', $product->slug),
            ],
        ];
    }

    public function calculateSeoScore(): int
    {
        $score = 0;

        // Title optimization (25 points)
        if ($this->meta_title) {
            $titleLength = strlen($this->meta_title);
            if ($titleLength >= 30 && $titleLength <= 60) {
                $score += 25;
            } elseif ($titleLength >= 20 && $titleLength <= 70) {
                $score += 15;
            } elseif ($titleLength > 0) {
                $score += 5;
            }
        }

        // Description optimization (25 points)
        if ($this->meta_description) {
            $descLength = strlen($this->meta_description);
            if ($descLength >= 120 && $descLength <= 160) {
                $score += 25;
            } elseif ($descLength >= 100 && $descLength <= 180) {
                $score += 15;
            } elseif ($descLength > 0) {
                $score += 5;
            }
        }

        // Keywords (15 points)
        if ($this->focus_keyword && $this->meta_title && 
            stripos($this->meta_title, $this->focus_keyword) !== false) {
            $score += 15;
        }

        // Open Graph (15 points)
        if ($this->og_title && $this->og_description) {
            $score += 15;
        }

        // Structured data (10 points)
        if ($this->structured_data && !empty($this->structured_data)) {
            $score += 10;
        }

        // Canonical URL (10 points)
        if ($this->canonical_url) {
            $score += 10;
        }

        $this->seo_score = $score;
        $this->save();

        return $score;
    }

    public function scopeWithLowScore($query, int $threshold = 70)
    {
        return $query->where('seo_score', '<', $threshold);
    }
}