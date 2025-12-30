<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_credits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_payment_id')->constrained('fee_payments')->cascadeOnDelete();
            $table->foreignId('applied_to_fee_id')->nullable()->constrained('fees')->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['available', 'applied'])->default('available');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_credits');
    }
};
