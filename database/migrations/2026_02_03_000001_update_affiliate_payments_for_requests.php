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
        Schema::table('affiliate_payments', function (Blueprint $table) {
            // Make admin ID nullable since requests start without an admin action
            $table->unsignedBigInteger('paid_by_admin_id')->nullable()->change();
            
            // Add status column
            $table->enum('status', ['pending', 'paid', 'rejected'])->default('paid')->after('amount');
            
            // Add payment details (e.g. PIX key)
            $table->string('payment_details')->nullable()->after('status')->comment('Chave PIX ou dados bancários');
            
            // Add rejection reason
            $table->text('rejection_reason')->nullable()->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('affiliate_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('paid_by_admin_id')->nullable(false)->change();
            $table->dropColumn(['status', 'payment_details', 'rejection_reason']);
        });
    }
};
