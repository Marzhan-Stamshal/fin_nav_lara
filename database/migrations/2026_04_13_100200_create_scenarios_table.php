<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scenarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('description')->nullable();
            $table->decimal('extra_budget_monthly', 14, 2)->default(0);
            $table->decimal('extra_budget_one_time', 14, 2)->default(0);
            $table->string('strategy_type')->default('custom');
            $table->decimal('result_total_savings', 14, 2)->default(0);
            $table->decimal('result_monthly_benefit', 14, 2)->default(0);
            $table->json('result_payload')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scenarios');
    }
};
