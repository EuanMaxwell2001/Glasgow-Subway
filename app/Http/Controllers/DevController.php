<?php

namespace App\Http\Controllers;

use App\Models\ServiceUpdate;
use App\Services\DisruptionParser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DevController extends Controller
{
    /**
     * Inject a test disruption (local environment only)
     */
    public function injectDisruption(Request $request, DisruptionParser $parser): JsonResponse
    {
        // Only allow in local environment
        if (config('app.env') !== 'local') {
            return response()->json([
                'error' => 'This endpoint is only available in local environment',
            ], 403);
        }

        // Validate input
        $validated = $request->validate([
            'disruptionType' => 'required|string',
            'title' => 'required|string',
            'snippet' => 'nullable|string',
            'publishedDate' => 'nullable|string',
            'url' => 'nullable|string',
        ]);

        // Add defaults
        $disruption = array_merge([
            'snippet' => '',
            'publishedDate' => now()->format('d/m/Y'),
            'url' => 'https://www.spt.co.uk/test',
        ], $validated);

        // Generate source ID
        $sourceId = ServiceUpdate::generateSourceId($disruption);

        // Check if already exists
        if (ServiceUpdate::where('source_id', $sourceId)->exists()) {
            return response()->json([
                'error' => 'This disruption already exists',
                'source_id' => $sourceId,
            ], 409);
        }

        // Save to database
        try {
            $publishedDate = null;
            if ($disruption['publishedDate']) {
                $publishedDate = \Carbon\Carbon::createFromFormat('d/m/Y', $disruption['publishedDate']);
            }

            $serviceUpdate = ServiceUpdate::create([
                'source' => 'dev_injection',
                'source_id' => $sourceId,
                'disruption_type' => $disruption['disruptionType'],
                'title' => $disruption['title'],
                'snippet' => $disruption['snippet'],
                'url' => $disruption['url'],
                'published_date' => $publishedDate,
                'fetched_at' => now(),
                'raw_json' => $disruption,
            ]);

            // Parse and update line statuses
            $parsed = $parser->parse($disruption);
            $parser->updateLineStatuses($parsed, $sourceId);

            Log::info('Test disruption injected', [
                'source_id' => $sourceId,
                'parsed' => $parsed,
            ]);

            return response()->json([
                'success' => true,
                'source_id' => $sourceId,
                'parsed' => $parsed,
                'service_update' => [
                    'id' => $serviceUpdate->id,
                    'title' => $serviceUpdate->title,
                    'fetched_at' => $serviceUpdate->fetched_at->toIso8601String(),
                ],
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to inject test disruption', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to inject disruption: ' . $e->getMessage(),
            ], 500);
        }
    }
}
