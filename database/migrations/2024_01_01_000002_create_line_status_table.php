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
        Schema::create('line_status', function (Blueprint $table) {
            $table->id();
            $table->enum('line', ['inner', 'outer', 'system'])->unique();
            $table->enum('status', ['running', 'suspended', 'disrupted', 'unknown'])->default('unknown');
            $table->text('message')->nullable();
            $table->timestamp('last_update_at')->nullable();
            $table->string('last_source_id')->nullable();
            $table->timestamps();

            $table->index('line');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('line_status');
    }
};
