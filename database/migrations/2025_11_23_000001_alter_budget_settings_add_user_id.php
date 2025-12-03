<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('budget_settings', function (Blueprint $table) {
            // Add user_id to store per-user settings
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
            $table->unique('user_id');
            // Optionally add foreign key constraint if desired
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('budget_settings', function (Blueprint $table) {
            // Drop constraints/columns in reverse
            $table->dropUnique(['user_id']);
            // $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
