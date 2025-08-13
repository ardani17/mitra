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
        Schema::table('employees', function (Blueprint $table) {
            // Contact Information
            $table->string('email')->nullable()->after('name');
            $table->string('phone', 20)->nullable()->change();
            $table->text('address')->nullable()->change();
            
            // Additional Personal Information
            $table->date('birth_date')->nullable()->after('hire_date');
            $table->enum('gender', ['male', 'female'])->nullable()->after('birth_date');
            $table->string('id_number', 50)->nullable()->after('gender')->comment('NIK/KTP');
            
            // Emergency Contact
            $table->string('emergency_contact_name')->nullable()->after('address');
            $table->string('emergency_contact_phone', 20)->nullable()->after('emergency_contact_name');
            $table->string('emergency_contact_relation', 50)->nullable()->after('emergency_contact_phone');
            
            // Employment Details
            $table->string('employment_type', 50)->default('permanent')->after('status')->comment('permanent, contract, freelance');
            $table->date('contract_end_date')->nullable()->after('employment_type');
            $table->string('bank_name', 100)->nullable()->after('daily_rate');
            $table->string('bank_account_number', 50)->nullable()->after('bank_name');
            $table->string('bank_account_name')->nullable()->after('bank_account_number');
            
            // Profile
            $table->string('avatar')->nullable()->after('notes')->comment('Profile photo path');
            
            // Additional indexes
            $table->index(['email']);
            $table->index(['employment_type']);
            $table->index(['department']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'email',
                'birth_date',
                'gender',
                'id_number',
                'emergency_contact_name',
                'emergency_contact_phone',
                'emergency_contact_relation',
                'employment_type',
                'contract_end_date',
                'bank_name',
                'bank_account_number',
                'bank_account_name',
                'avatar'
            ]);
            
            $table->dropIndex(['email']);
            $table->dropIndex(['employment_type']);
            $table->dropIndex(['department']);
        });
    }
};