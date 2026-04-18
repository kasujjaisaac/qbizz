<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-slate-500">Invoices</p>
            <h1 class="text-2xl font-semibold text-slate-950">Create Invoice</h1>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('invoices.store') }}">
        @csrf
        @include('invoices.partials.form', ['invoice' => $invoice, 'submitLabel' => 'Create invoice'])
    </form>
</x-app-layout>
