<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-slate-500">Invoices</p>
            <h1 class="text-2xl font-semibold text-slate-950">Edit {{ $invoice->invoice_number }}</h1>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('invoices.update', $invoice) }}">
        @csrf
        @method('PUT')
        @include('invoices.partials.form', ['invoice' => $invoice, 'submitLabel' => 'Save invoice changes'])
    </form>
</x-app-layout>
