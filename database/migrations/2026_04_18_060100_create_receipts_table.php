<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->string('receipt_number')->unique();
            $table->string('payer_name');
            $table->string('payer_email')->nullable();
            $table->string('payer_phone')->nullable();
            $table->text('payer_address')->nullable();
            $table->decimal('amount_received', 12, 2);
            $table->date('payment_date');
            $table->string('payment_method')->nullable();
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('invoice_total_snapshot', 12, 2)->nullable();
            $table->decimal('balance_before', 12, 2)->nullable();
            $table->decimal('balance_after', 12, 2)->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();
        });

        $settledInvoices = DB::table('invoices')
            ->where('status', 'settled')
            ->orderBy('id')
            ->get();

        foreach ($settledInvoices as $invoice) {
            DB::table('receipts')->insert([
                'user_id' => $invoice->user_id,
                'business_profile_id' => $invoice->business_profile_id,
                'invoice_id' => $invoice->id,
                'receipt_number' => sprintf('RCP-BACKFILL-%06d', $invoice->id),
                'payer_name' => $invoice->customer_name,
                'payer_email' => $invoice->customer_email,
                'payer_phone' => $invoice->customer_phone,
                'payer_address' => $invoice->customer_address,
                'amount_received' => $invoice->total_amount,
                'payment_date' => $invoice->settled_at ? date('Y-m-d', strtotime((string) $invoice->settled_at)) : date('Y-m-d', strtotime((string) $invoice->issue_date)),
                'invoice_total_snapshot' => $invoice->total_amount,
                'balance_before' => $invoice->total_amount,
                'balance_after' => 0,
                'issued_at' => $invoice->settled_at ?? $invoice->created_at,
                'created_at' => $invoice->created_at,
                'updated_at' => $invoice->updated_at,
            ]);
        }

        DB::table('invoices')
            ->where('status', 'settled')
            ->update([
                'paid_amount' => DB::raw('total_amount'),
            ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};
