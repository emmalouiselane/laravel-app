<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_occurrences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->unique(['payment_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_occurrences');
    }
};
