<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scenario_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scenario_id')->constrained()->cascadeOnDelete();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('priority')->default(1);
            $table->unsignedInteger('projected_close_months')->nullable();
            $table->timestamps();

            $table->unique(['scenario_id', 'loan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scenario_loans');
    }
};
