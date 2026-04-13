<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('bank_name');
            $table->string('loan_type')->default('наличные');
            $table->string('status')->default('активный');
            $table->decimal('principal_initial', 14, 2);
            $table->decimal('early_payoff_amount', 14, 2);
            $table->decimal('monthly_payment', 14, 2);
            $table->decimal('interest_rate_annual', 7, 3)->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->date('next_payment_date')->nullable();
            $table->unsignedInteger('months_total')->nullable();
            $table->unsignedInteger('months_paid')->default(0);
            $table->string('group_name')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'bank_name']);
            $table->index(['user_id', 'group_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
