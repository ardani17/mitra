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
        Schema::create('revenue_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('revenue_id')->constrained('project_revenues')->onDelete('cascade');
            $table->string('item_name');
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->enum('type', ['service', 'product', 'other'])->default('service');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revenue_items');
    }
};
