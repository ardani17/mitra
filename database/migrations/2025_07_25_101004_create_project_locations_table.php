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
        Schema::create('project_locations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Nama lokasi (contoh: SBU, Jakarta Pusat, dll)
            $table->text('description')->nullable(); // Deskripsi tambahan lokasi
            $table->integer('usage_count')->default(1); // Berapa kali lokasi ini digunakan
            $table->timestamp('last_used_at')->useCurrent(); // Kapan terakhir digunakan
            $table->timestamps();
            
            $table->index('name'); // Index untuk pencarian cepat
            $table->index('usage_count'); // Index untuk sorting berdasarkan popularitas
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_locations');
    }
};
