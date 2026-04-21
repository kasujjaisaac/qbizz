<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-slate-500">Quotations</p>
            <h1 class="text-2xl font-semibold text-slate-950">Edit {{ $quotation->quotation_number }}</h1>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('quotations.update', $quotation) }}">
        @csrf
        @method('PUT')
        @include('quotations.partials.form', ['quotation' => $quotation, 'submitLabel' => 'Save quotation changes'])
    </form>
</x-app-layout>
