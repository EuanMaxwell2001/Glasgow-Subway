<?php

namespace App\Services;

use App\Models\LineStatus;
use Illuminate\Support\Facades\Log;

class DisruptionParser
{
    /**
     * Parse a disruption and determine affected lines and status
     * 
     * @param array $disruption The disruption data
     * @return array ['affected' => ['inner', 'outer'], 'status' => 'suspended', 'message' => '...']
     */
    public function parse(array $disruption): array
    {
        $title = $disruption['title'] ?? '';
        $snippet = $disruption['snippet'] ?? '';
        
        // Combine title and snippet for analysis
        $text = strtolower($title . "\n" . $snippet);

        // Determine affected lines
        $affected = $this->determineAffectedLines($text);

        // Determine status
        $status = $this->determineStatus($text);

        // Generate user-friendly message
        $message = $this->generateMessage($title, $snippet);

        return [
            'affected' => $affected,
            'status' => $status,
            'message' => $message,
        ];
    }

    /**
     * Determine which lines are affected
     */
    private function determineAffectedLines(string $text): array
    {
        $affected = [];

        // Check for both circles
        if (
            str_contains($text, 'inner and outer') ||
            str_contains($text, 'inner & outer') ||
            str_contains($text, 'both circles') ||
            str_contains($text, 'all services') ||
            str_contains($text, 'entire network') ||
            str_contains($text, 'whole network')
        ) {
            return [LineStatus::LINE_INNER, LineStatus::LINE_OUTER];
        }

        // Check for specific lines
        if (str_contains($text, 'outer')) {
            $affected[] = LineStatus::LINE_OUTER;
        }

        if (str_contains($text, 'inner')) {
            $affected[] = LineStatus::LINE_INNER;
        }

        // If no specific line mentioned, mark as system-wide
        if (empty($affected)) {
            $affected[] = LineStatus::LINE_SYSTEM;
        }

        return $affected;
    }

    /**
     * Determine the status based on keywords
     */
    private function determineStatus(string $text): string
    {
        // Check for suspended/closed
        $suspendedKeywords = [
            'suspended',
            'no service',
            'not running',
            'closed',
            'shut down',
            'closure',
        ];

        foreach ($suspendedKeywords as $keyword) {
            if (str_contains($text, $keyword)) {
                return LineStatus::STATUS_SUSPENDED;
            }
        }

        // Check for resumed/running normally
        $runningKeywords = [
            'resumed',
            'operating normally',
            'running normally',
            'restored',
            'service restored',
            'back to normal',
            'normal service',
        ];

        foreach ($runningKeywords as $keyword) {
            if (str_contains($text, $keyword)) {
                return LineStatus::STATUS_RUNNING;
            }
        }

        // Check for disruptions/delays
        $disruptedKeywords = [
            'delays',
            'delay',
            'disruption',
            'part suspended',
            'reduced service',
            'limited service',
            'slower than usual',
            'experiencing issues',
        ];

        foreach ($disruptedKeywords as $keyword) {
            if (str_contains($text, $keyword)) {
                return LineStatus::STATUS_DISRUPTED;
            }
        }

        // If we can't determine, return unknown
        return LineStatus::STATUS_UNKNOWN;
    }

    /**
     * Generate a user-friendly message
     */
    private function generateMessage(string $title, string $snippet): string
    {
        // Use title as primary message
        $message = trim($title);

        // If title is too short or missing, use snippet
        if (strlen($message) < 10 && !empty($snippet)) {
            $message = trim($snippet);
        }

        // Truncate if too long
        if (strlen($message) > 200) {
            $message = substr($message, 0, 197) . '...';
        }

        return $message ?: 'Service update available';
    }

    /**
     * Update line statuses based on parsed disruption
     */
    public function updateLineStatuses(array $parsedData, string $sourceId): void
    {
        $affected = $parsedData['affected'];
        $status = $parsedData['status'];
        $message = $parsedData['message'];

        // Only update if we have a definite status (not unknown)
        if ($status === LineStatus::STATUS_UNKNOWN) {
            Log::info("Skipping line status update for source {$sourceId}: status is unknown");
            return;
        }

        foreach ($affected as $line) {
            Log::info("Updating {$line} line to {$status}: {$message}");
            
            LineStatus::updateLineStatus(
                $line,
                $status,
                $message,
                $sourceId
            );
        }
    }
}
