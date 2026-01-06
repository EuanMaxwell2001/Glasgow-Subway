<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LineStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $lines = [
            [
                'line' => 'inner',
                'status' => 'unknown',
                'message' => 'No information available',
                'last_update_at' => null,
                'last_source_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'line' => 'outer',
                'status' => 'unknown',
                'message' => 'No information available',
                'last_update_at' => null,
                'last_source_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'line' => 'system',
                'status' => 'unknown',
                'message' => 'No information available',
                'last_update_at' => null,
                'last_source_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($lines as $line) {
            DB::table('line_status')->updateOrInsert(
                ['line' => $line['line']],
                $line
            );
        }
    }
}
