<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'yearly'])->nullable()->after('repeatable');
            $table->date('repeat_end_date')->nullable()->after('frequency');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['frequency', 'repeat_end_date']);
        });
    }
};
