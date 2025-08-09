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
        Schema::table('todos', function (Blueprint $table) {
            $table->string('type')->default('one_time')->comment('one_time, recurring, habit');
            $table->json('recurrence_pattern')->nullable()->comment('For recurring tasks: daily, weekly, monthly, etc.');
            $table->date('recurrence_ends_at')->nullable()->comment('When the recurrence should end');
            $table->integer('target_count')->default(1)->comment('For habits: Target number of times to complete');
            $table->string('frequency')->nullable()->comment('For habits: daily, weekly, etc.');
            $table->integer('current_streak')->default(0)->comment('For habits: Current streak count');
            $table->integer('longest_streak')->default(0)->comment('For habits: Longest streak achieved');
            $table->date('last_completed_at')->nullable()->comment('For habits: When this was last completed');
            $table->boolean('is_skippable')->default(true)->comment('For habits: Whether the habit can be skipped');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('todos', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'recurrence_pattern',
                'recurrence_ends_at',
                'target_count',
                'frequency',
                'current_streak',
                'longest_streak',
                'last_completed_at',
                'is_skippable'
            ]);
        });
    }
};
