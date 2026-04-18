<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('dashboard', [
            'businessProfile' => $user->businessProfile,
            'totalInvoiced' => (float) $user->invoices()->sum('total_amount'),
            'moneyReceived' => (float) $user->receipts()->sum('amount_received'),
            'outstandingBalance' => (float) $user->invoices()
                ->open()
                ->select(DB::raw('COALESCE(SUM(total_amount - paid_amount), 0) as aggregate'))
                ->value('aggregate'),
            'activeInvoiceCount' => $user->invoices()->open()->count(),
            'settledInvoiceCount' => $user->invoices()->settled()->count(),
            'receiptCount' => $user->receipts()->count(),
            'linkedReceiptCount' => $user->receipts()->whereNotNull('invoice_id')->count(),
            'recentInvoices' => $user->invoices()->latest('issue_date')->latest('id')->take(5)->get(),
        ]);
    }
}
