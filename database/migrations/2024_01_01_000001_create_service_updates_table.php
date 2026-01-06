<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('service_updates', function (Blueprint $table) {
            $table->id();
            $table->string('source', 100)->default('spt_disruptions');
            $table->string('source_id', 100)->unique();
            $table->string('disruption_type', 100);
            $table->text('title');
            $table->text('snippet')->nullable();
            $table->text('url')->nullable();
            $table->date('published_date')->nullable();
            $table->timestamp('fetched_at');
            $table->json('raw_json')->nullable();
            $table->timestamps();

            $table->index('disruption_type');
            $table->index('fetched_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_updates');
    }
};
