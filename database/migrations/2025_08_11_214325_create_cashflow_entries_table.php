<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cashflow_entries', function (Blueprint $table) {
            $table->id();
            $table->enum('reference_type', ['billing', 'expense', 'manual', 'adjustment']);
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('category_id')->constrained('cashflow_categories')->onDelete('restrict');
            $table->date('transaction_date');
            $table->text('description');
            $table->decimal('amount', 15, 2);
            $table->enum('type', ['income', 'expense']);
            $table->string('payment_method')->nullable(); // 'cash', 'bank_transfer', 'check', etc.
            $table->string('account_code')->nullable(); // untuk integrasi akuntansi
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('confirmed');
            $table->timestamp('confirmed_at')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes untuk performa
            $table->index(['transaction_date', 'type']);
            $table->index(['reference_type', 'reference_id']);
            $table->index(['project_id', 'transaction_date']);
            $table->index(['status', 'transaction_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashflow_entries');
    }
};
