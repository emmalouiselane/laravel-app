<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql' && Schema::hasTable('budget_settings')) {
            // Align the sequence with the current MAX(id)
            $maxId = (int) (DB::table('budget_settings')->max('id') ?? 0);
            // Ensure sequence is at least 1 when table is empty
            DB::statement("SELECT setval(pg_get_serial_sequence('budget_settings','id'), GREATEST(?, 1))", [$maxId]);
        }
    }

    public function down(): void
    {
        // No-op: sequence alignment is safe to leave as-is
    }
};
