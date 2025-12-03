<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('budget_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('pay_period_start_day')->default(28); // 1-31
            $table->timestamps();
        });

        // Seed a singleton row with default values
        DB::table('budget_settings')->insert([
            'id' => 1,
            'pay_period_start_day' => 28,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_settings');
    }
};
