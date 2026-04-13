<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->date('payment_date');
            $table->decimal('planned_amount', 14, 2);
            $table->decimal('actual_amount', 14, 2)->nullable();
            $table->decimal('extra_payment', 14, 2)->default(0);
            $table->string('status')->default('оплачен');
            $table->boolean('is_mass_action')->default(false);
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'payment_date']);
            $table->index(['loan_id', 'payment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
