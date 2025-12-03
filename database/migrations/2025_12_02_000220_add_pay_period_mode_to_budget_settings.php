<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('budget_settings', function (Blueprint $table) {
            $table->enum('pay_period_mode', ['monthly', 'weekly'])->default('monthly')->after('pay_period_start_day');
        });
    }

    public function down(): void
    {
        Schema::table('budget_settings', function (Blueprint $table) {
            $table->dropColumn(['pay_period_mode']);
        });
    }
};
