<?php

namespace App\Services;

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\Rating;
use App\Models\BrowsingHistory;

class RecommendationService
{
    public function getRecommendations(User $user, $limit = 5)
    {
        $purchasedProducts = $this->getPurchasedProducts($user);
        $browsedProducts = $this->getBrowsedProducts($user);
        $ratedProducts = $this->getRatedProducts($user);

        $recommendedProducts = collect();

        // Recommend based on purchase history
        $recommendedProducts = $recommendedProducts->merge($this->getRelatedProducts($purchasedProducts));

        // Recommend based on browsing history
        $recommendedProducts = $recommendedProducts->merge($this->getRelatedProducts($browsedProducts));

        // Recommend based on highly rated products
        $recommendedProducts = $recommendedProducts->merge($this->getHighlyRatedProducts($ratedProducts));

        // Remove duplicates and products already purchased or browsed
        $recommendedProducts = $recommendedProducts->unique('id')
            ->diff($purchasedProducts)
            ->diff($browsedProducts)
            ->sortByDesc('rating')
            ->take($limit);

        return $recommendedProducts;
    }

    private function getPurchasedProducts(User $user)
    {
        return $user->orders()->with('products')->get()->pluck('products')->flatten()->unique('id');
    }

    private function getBrowsedProducts(User $user)
    {
        return $user->browsingHistory()->with('product')->orderBy('created_at', 'desc')->take(20)->get()->pluck('product');
    }

    private function getRatedProducts(User $user)
    {
        return $user->ratings()->with('product')->get()->pluck('product');
    }

    private function getRelatedProducts($products)
    {
        return Product::whereHas('categories', function ($query) use ($products) {
            $query->whereIn('id', $products->pluck('categories.*.id')->flatten());
        })->get();
    }

    private function getHighlyRatedProducts($products)
    {
        return Product::whereIn('id', $products->pluck('id'))
            ->withAvg('ratings as rating', 'rating')
            ->orderByDesc('rating')
            ->take(10)
            ->get();
    }
}