<?php

namespace App\Http\Controllers;

use App\Models\LineStatus;
use App\Models\ServiceUpdate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatusController extends Controller
{
    /**
     * Get current line status
     */
    public function getStatus(): JsonResponse
    {
        $statuses = LineStatus::all()->keyBy('line');

        // Get last poll time
        $lastChecked = DB::table('poller_metadata')
            ->where('key', 'last_successful_poll')
            ->value('value');

        $lastCheckedAt = $lastChecked ? Carbon::parse($lastChecked) : null;
        
        // Check if data is stale
        $stalenessThreshold = config('spt.staleness_threshold', 10);
        $isStale = !$lastCheckedAt || 
                   $lastCheckedAt->diffInMinutes(now()) > $stalenessThreshold;

        return response()->json([
            'inner' => [
                'status' => $statuses->get('inner')?->status ?? 'unknown',
                'message' => $statuses->get('inner')?->message ?? 'No information available',
                'updated_at' => $statuses->get('inner')?->last_update_at?->toIso8601String(),
            ],
            'outer' => [
                'status' => $statuses->get('outer')?->status ?? 'unknown',
                'message' => $statuses->get('outer')?->message ?? 'No information available',
                'updated_at' => $statuses->get('outer')?->last_update_at?->toIso8601String(),
            ],
            'meta' => [
                'last_checked_at' => $lastCheckedAt?->toIso8601String(),
                'stale' => $isStale,
            ],
        ]);
    }

    /**
     * Get recent service updates
     */
    public function getUpdates(Request $request): JsonResponse
    {
        $limit = $request->query('limit', 20);
        $limit = min(max(1, (int)$limit), 100); // Between 1 and 100

        // Show ALL service updates, not just subway
        $updates = ServiceUpdate::orderBy('fetched_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($update) {
                return [
                    'title' => $update->title,
                    'snippet' => $update->snippet,
                    'published_date' => $update->published_date?->format('d/m/Y'),
                    'disruption_type' => $update->disruption_type,
                    'url' => $update->url,
                    'fetched_at' => $update->fetched_at->toIso8601String(),
                ];
            });

        return response()->json([
            'updates' => $updates,
            'count' => $updates->count(),
        ]);
    }
}
