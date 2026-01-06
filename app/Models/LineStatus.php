<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LineStatus extends Model
{
    use HasFactory;

    protected $table = 'line_status';

    protected $fillable = [
        'line',
        'status',
        'message',
        'last_update_at',
        'last_source_id',
    ];

    protected $casts = [
        'last_update_at' => 'datetime',
    ];

    // Line constants
    const LINE_INNER = 'inner';
    const LINE_OUTER = 'outer';
    const LINE_SYSTEM = 'system';

    // Status constants
    const STATUS_RUNNING = 'running';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_DISRUPTED = 'disrupted';
    const STATUS_UNKNOWN = 'unknown';

    /**
     * Get status for a specific line
     */
    public static function getLineStatus(string $line): ?self
    {
        return self::where('line', $line)->first();
    }

    /**
     * Update status for a line
     */
    public static function updateLineStatus(
        string $line,
        string $status,
        ?string $message = null,
        ?string $sourceId = null
    ): void {
        self::updateOrCreate(
            ['line' => $line],
            [
                'status' => $status,
                'message' => $message,
                'last_update_at' => now(),
                'last_source_id' => $sourceId,
            ]
        );
    }

    /**
     * Get all line statuses as array
     */
    public static function getAllStatuses(): array
    {
        return self::all()
            ->keyBy('line')
            ->map(fn($status) => [
                'status' => $status->status,
                'message' => $status->message,
                'updated_at' => $status->last_update_at?->toIso8601String(),
            ])
            ->toArray();
    }
}
