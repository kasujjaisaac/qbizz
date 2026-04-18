<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-slate-500">Receipts</p>
            <h1 class="text-2xl font-semibold text-slate-950">Issue Receipt</h1>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('receipts.store') }}">
        @csrf
        @include('receipts.partials.form', ['receipt' => $receipt, 'openInvoices' => $openInvoices, 'selectedInvoice' => $selectedInvoice, 'submitLabel' => 'Issue receipt'])
    </form>
</x-app-layout>
