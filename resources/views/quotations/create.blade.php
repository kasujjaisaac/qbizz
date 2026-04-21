<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-slate-500">Quotations</p>
            <h1 class="text-2xl font-semibold text-slate-950">Create Quotation</h1>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('quotations.store') }}">
        @csrf
        @include('quotations.partials.form', ['quotation' => $quotation, 'submitLabel' => 'Create quotation'])
    </form>
</x-app-layout>
