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
        Schema::create('billing_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('billing_batch_id')->constrained('billing_batches')->onDelete('cascade');
            $table->enum('stage', [
                'initial',
                'area_revision',
                'regional_revision',
                'supporting_document'
            ]);
            $table->string('document_type')->nullable(); // invoice, tax_invoice, sp, etc
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_size')->nullable();
            $table->string('mime_type')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            // Indexes
            $table->index(['billing_batch_id', 'stage']);
            $table->index('document_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_documents');
    }
};
