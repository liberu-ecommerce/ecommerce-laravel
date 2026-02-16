<?php

namespace App\Services;

use App\Models\CustomerSegment;
use App\Models\User;
use Illuminate\Support\Collection;

class CustomerSegmentationService
{
    /**
     * Get all segments a user belongs to
     */
    public function getUserSegments(int $userId): Collection
    {
        return CustomerSegment::whereHas('members', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->get();
    }

    /**
     * Check if user belongs to a specific segment
     */
    public function isUserInSegment(int $userId, int $segmentId): bool
    {
        return CustomerSegment::find($segmentId)
            ?->members()
            ->where('user_id', $userId)
            ->exists() ?? false;
    }

    /**
     * Get targeted users for a campaign
     */
    public function getUsersForSegments(array $segmentIds): Collection
    {
        return User::whereHas('customerSegments', function ($query) use ($segmentIds) {
            $query->whereIn('customer_segments.id', $segmentIds);
        })->get();
    }

    /**
     * Create a new segment
     */
    public function createSegment(string $name, array $conditions, string $matchType = 'all'): CustomerSegment
    {
        $segment = CustomerSegment::create([
            'name' => $name,
            'conditions' => $conditions,
            'match_type' => $matchType,
            'is_active' => true,
        ]);

        $segment->calculateMembers();

        return $segment;
    }

    /**
     * Recalculate all active segments
     */
    public function recalculateAllSegments(): void
    {
        CustomerSegment::active()->each(function ($segment) {
            $segment->calculateMembers();
        });
    }

    /**
     * Get segment statistics
     */
    public function getSegmentStats(int $segmentId): array
    {
        $segment = CustomerSegment::with('members.customerMetric')->find($segmentId);

        if (!$segment) {
            return [];
        }

        $members = $segment->members;

        return [
            'total_members' => $members->count(),
            'average_ltv' => $members->avg('customerMetric.lifetime_value') ?? 0,
            'total_orders' => $members->sum('customerMetric.total_orders') ?? 0,
            'average_orders' => $members->avg('customerMetric.total_orders') ?? 0,
        ];
    }
}
