<?php

namespace App\Console\Commands;

use App\Models\ServiceUpdate;
use App\Services\SptApiClient;
use App\Services\DisruptionParser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PollSptDisruptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spt:poll {--dry-run : Run without saving to database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Poll SPT API for disruptions and update line status';

    private SptApiClient $apiClient;
    private DisruptionParser $parser;

    public function __construct(SptApiClient $apiClient, DisruptionParser $parser)
    {
        parent::__construct();
        $this->apiClient = $apiClient;
        $this->parser = $parser;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting SPT disruptions poll...');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No data will be saved');
        }

        try {
            // Fetch all disruptions
            $this->info('Fetching disruptions from SPT API...');
            $disruptions = $this->apiClient->fetchAllDisruptions();
            $this->info("Fetched " . count($disruptions) . " total disruptions");

            if (empty($disruptions)) {
                $this->warn('No disruptions found');
                $this->updateLastPollTime($dryRun);
                return Command::SUCCESS;
            }

            $newCount = 0;
            $subwayCount = 0;

            // Process each disruption
            foreach ($disruptions as $disruption) {
                $sourceId = ServiceUpdate::generateSourceId($disruption);

                // Check if already exists
                if (!$dryRun && ServiceUpdate::where('source_id', $sourceId)->exists()) {
                    continue;
                }

                $newCount++;

                // Check if subway-related
                $disruptionType = strtolower($disruption['disruptionType'] ?? '');
                $title = strtolower($disruption['title'] ?? '');
                $snippet = strtolower($disruption['snippet'] ?? '');
                
                $isSubway = $disruptionType === 'subway' || 
                           str_contains($title, 'subway') || 
                           str_contains($snippet, 'subway');

                if (!$isSubway) {
                    $this->line("  Skipping non-subway: {$disruption['title']}");
                    
                    if (!$dryRun) {
                        $this->saveServiceUpdate($disruption, $sourceId);
                    }
                    
                    continue;
                }

                $subwayCount++;
                $this->info("  Processing subway disruption: {$disruption['title']}");

                // Parse the disruption
                $parsed = $this->parser->parse($disruption);
                
                $this->line("    Affected: " . implode(', ', $parsed['affected']));
                $this->line("    Status: {$parsed['status']}");
                $this->line("    Message: {$parsed['message']}");

                if (!$dryRun) {
                    // Save to database
                    $this->saveServiceUpdate($disruption, $sourceId);

                    // Update line statuses
                    $this->parser->updateLineStatuses($parsed, $sourceId);
                }
            }

            $this->info("\nSummary:");
            $this->info("  New disruptions: {$newCount}");
            $this->info("  Subway-related: {$subwayCount}");

            if (!$dryRun) {
                $this->updateLastPollTime($dryRun);
                $this->info('Poll completed successfully');
            } else {
                $this->warn('DRY RUN - No changes saved');
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Poll failed: ' . $e->getMessage());
            Log::error('SPT poll failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * Save a service update to the database
     */
    private function saveServiceUpdate(array $disruption, string $sourceId): void
    {
        $publishedDate = null;
        if (isset($disruption['publishedDate'])) {
            try {
                $publishedDate = Carbon::createFromFormat('d/m/Y', $disruption['publishedDate']);
            } catch (\Exception $e) {
                Log::warning("Failed to parse published date: {$disruption['publishedDate']}");
            }
        }

        ServiceUpdate::create([
            'source' => 'spt_disruptions',
            'source_id' => $sourceId,
            'disruption_type' => $disruption['disruptionType'] ?? 'unknown',
            'title' => $disruption['title'] ?? '',
            'snippet' => $disruption['snippet'] ?? '',
            'url' => $disruption['url'] ?? '',
            'published_date' => $publishedDate,
            'fetched_at' => now(),
            'raw_json' => $disruption,
        ]);
    }

    /**
     * Update the last successful poll timestamp
     */
    private function updateLastPollTime(bool $dryRun): void
    {
        if ($dryRun) {
            return;
        }

        DB::table('poller_metadata')
            ->updateOrInsert(
                ['key' => 'last_successful_poll'],
                [
                    'value' => now()->toIso8601String(),
                    'updated_at' => now(),
                ]
            );
    }
}
