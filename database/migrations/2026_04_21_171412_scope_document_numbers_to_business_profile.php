<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->index('user_id');
            $table->dropUnique('invoices_user_id_invoice_number_unique');
            $table->unique(['business_profile_id', 'invoice_number']);
        });

        Schema::table('receipts', function (Blueprint $table): void {
            $table->index('user_id');
            $table->dropUnique('receipts_user_id_receipt_number_unique');
            $table->unique(['business_profile_id', 'receipt_number']);
        });

        Schema::table('quotations', function (Blueprint $table): void {
            $table->index('user_id');
            $table->dropUnique('quotations_user_id_quotation_number_unique');
            $table->unique(['business_profile_id', 'quotation_number']);
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table): void {
            $table->index('business_profile_id');
            $table->dropUnique('quotations_business_profile_id_quotation_number_unique');
            $table->unique(['user_id', 'quotation_number']);
        });

        Schema::table('receipts', function (Blueprint $table): void {
            $table->index('business_profile_id');
            $table->dropUnique('receipts_business_profile_id_receipt_number_unique');
            $table->unique(['user_id', 'receipt_number']);
        });

        Schema::table('invoices', function (Blueprint $table): void {
            $table->index('business_profile_id');
            $table->dropUnique('invoices_business_profile_id_invoice_number_unique');
            $table->unique(['user_id', 'invoice_number']);
        });
    }
};
