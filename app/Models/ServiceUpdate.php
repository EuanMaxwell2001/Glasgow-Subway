<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServiceUpdate extends Model
{
    use HasFactory;

    protected $table = 'service_updates';

    protected $fillable = [
        'source',
        'source_id',
        'disruption_type',
        'title',
        'snippet',
        'url',
        'published_date',
        'fetched_at',
        'raw_json',
    ];

    protected $casts = [
        'published_date' => 'date',
        'fetched_at' => 'datetime',
        'raw_json' => 'array',
    ];

    /**
     * Generate a deterministic source ID from disruption data
     */
    public static function generateSourceId(array $data): string
    {
        $components = [
            $data['disruptionType'] ?? '',
            $data['title'] ?? '',
            $data['publishedDate'] ?? '',
            $data['url'] ?? '',
        ];

        return sha1(implode('|', $components));
    }

    /**
     * Check if this is a subway-related disruption
     */
    public function isSubwayRelated(): bool
    {
        // Check disruptionType first
        if (strtolower($this->disruption_type) === 'subway') {
            return true;
        }

        // Fallback to keyword matching in title/snippet
        $text = strtolower($this->title . ' ' . $this->snippet);
        return str_contains($text, 'subway');
    }

    /**
     * Scope to get only subway-related updates
     */
    public function scopeSubwayOnly($query)
    {
        return $query->where(function ($q) {
            $q->whereRaw('LOWER(disruption_type) = ?', ['subway'])
              ->orWhereRaw('LOWER(title) LIKE ?', ['%subway%'])
              ->orWhereRaw('LOWER(snippet) LIKE ?', ['%subway%']);
        });
    }

    /**
     * Scope to get recent updates
     */
    public function scopeRecent($query, int $limit = 20)
    {
        return $query->orderBy('fetched_at', 'desc')
                    ->orderBy('published_date', 'desc')
                    ->limit($limit);
    }
}
