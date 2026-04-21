@php
    $user = Auth::user();
@endphp

<aside
    x-cloak
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    class="fixed inset-y-0 left-0 z-40 flex w-[85vw] max-w-72 transform flex-col overflow-y-auto bg-slate-950 text-slate-100 transition duration-200 ease-out"
>
    <div class="flex items-center justify-between border-b border-white/10 px-6 py-5 lg:justify-start">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center border border-white/10 bg-white/5 p-2">
                <img src="{{ asset('images/qbizz-mark-transparent.png') }}" alt="Qbizz logo" class="h-full w-full object-contain">
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Business ERP</p>
                <p class="mt-1 text-xl font-semibold text-white">{{ config('app.name', 'Qbizz') }}</p>
            </div>
        </a>

        <button type="button" @click="sidebarOpen = false" class="inline-flex h-10 w-10 items-center justify-center text-slate-400 lg:hidden">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <nav class="flex-1 space-y-2 px-4 py-5 text-sm">
        <a
            href="{{ route('dashboard') }}"
            class="{{ request()->routeIs('dashboard') ? 'border-sky-400/40 bg-sky-400/10 text-white' : 'border-transparent text-slate-300 hover:border-white/10 hover:bg-white/5 hover:text-white' }} flex items-center gap-3 border px-4 py-3 font-medium transition"
        >
            <span class="inline-flex h-9 w-9 items-center justify-center border border-current/15">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12 12 4.5 20.25 12m-1.5 0v7.125A1.125 1.125 0 0 1 17.625 20.25h-3.75v-4.875h-3.75v4.875h-3.75A1.125 1.125 0 0 1 5.25 19.125V12" />
                </svg>
            </span>
            Dashboard
        </a>

        <a
            href="{{ route('business-profile.edit') }}"
            class="{{ request()->routeIs('business-profile.*') ? 'border-sky-400/40 bg-sky-400/10 text-white' : 'border-transparent text-slate-300 hover:border-white/10 hover:bg-white/5 hover:text-white' }} flex items-center gap-3 border px-4 py-3 font-medium transition"
        >
            <span class="inline-flex h-9 w-9 items-center justify-center border border-current/15">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 7.5h-9m9 4.5h-9m9 4.5h-5.25M6 3.75h12A2.25 2.25 0 0 1 20.25 6v12A2.25 2.25 0 0 1 18 20.25H6A2.25 2.25 0 0 1 3.75 18V6A2.25 2.25 0 0 1 6 3.75Z" />
                </svg>
            </span>
            Business Profile
        </a>

        <a
            href="{{ route('quotations.create') }}"
            class="{{ request()->routeIs('quotations.create') ? 'border-amber-300 bg-amber-500 text-white' : 'border-transparent text-slate-300 hover:border-amber-300/40 hover:bg-white/5 hover:text-white' }} flex items-center gap-3 border px-4 py-3 font-medium transition"
        >
            <span class="inline-flex h-9 w-9 items-center justify-center border border-current/15">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12M6 12h12" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75h9A2.25 2.25 0 0 1 18.75 6v12A2.25 2.25 0 0 1 16.5 20.25h-9A2.25 2.25 0 0 1 5.25 18V6A2.25 2.25 0 0 1 7.5 3.75Z" />
                </svg>
            </span>
            Create Quotation
        </a>

        <a
            href="{{ route('quotations.index') }}"
            class="{{ request()->routeIs('quotations.index') || request()->routeIs('quotations.edit') || request()->routeIs('quotations.show') ? 'border-amber-300/40 bg-amber-500/10 text-white' : 'border-transparent text-slate-300 hover:border-white/10 hover:bg-white/5 hover:text-white' }} flex items-center gap-3 border px-4 py-3 font-medium transition"
        >
            <span class="inline-flex h-9 w-9 items-center justify-center border border-current/15">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75h7.379a2.25 2.25 0 0 1 1.59.659l2.122 2.121a2.25 2.25 0 0 1 .659 1.591V19.5A2.25 2.25 0 0 1 17 21.75H7.5A2.25 2.25 0 0 1 5.25 19.5V6A2.25 2.25 0 0 1 7.5 3.75Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9.75h7.5M8.25 13.5h7.5M8.25 17.25h4.5" />
                </svg>
            </span>
            Quotations
        </a>

        <a
            href="{{ route('invoices.create') }}"
            class="{{ request()->routeIs('invoices.create') ? 'border-sky-400 bg-sky-500 text-white' : 'border-transparent text-slate-300 hover:border-sky-400/40 hover:bg-white/5 hover:text-white' }} flex items-center gap-3 border px-4 py-3 font-medium transition"
        >
            <span class="inline-flex h-9 w-9 items-center justify-center border border-current/15">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12M6 12h12" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75h9A2.25 2.25 0 0 1 18.75 6v12A2.25 2.25 0 0 1 16.5 20.25h-9A2.25 2.25 0 0 1 5.25 18V6A2.25 2.25 0 0 1 7.5 3.75Z" />
                </svg>
            </span>
            Create Invoice
        </a>

        <a
            href="{{ route('invoices.index') }}"
            class="{{ request()->routeIs('invoices.index') || request()->routeIs('invoices.edit') || request()->routeIs('invoices.show') ? 'border-sky-400/40 bg-sky-400/10 text-white' : 'border-transparent text-slate-300 hover:border-white/10 hover:bg-white/5 hover:text-white' }} flex items-center gap-3 border px-4 py-3 font-medium transition"
        >
            <span class="inline-flex h-9 w-9 items-center justify-center border border-current/15">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75h7.379a2.25 2.25 0 0 1 1.59.659l2.122 2.121a2.25 2.25 0 0 1 .659 1.591V19.5A2.25 2.25 0 0 1 17 21.75H7.5A2.25 2.25 0 0 1 5.25 19.5V6A2.25 2.25 0 0 1 7.5 3.75Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9.75h7.5M8.25 13.5h7.5M8.25 17.25h4.5" />
                </svg>
            </span>
            Invoices
        </a>

        <a
            href="{{ route('receipts.index') }}"
            class="{{ request()->routeIs('receipts.*') ? 'border-sky-400/40 bg-sky-400/10 text-white' : 'border-transparent text-slate-300 hover:border-white/10 hover:bg-white/5 hover:text-white' }} flex items-center gap-3 border px-4 py-3 font-medium transition"
        >
            <span class="inline-flex h-9 w-9 items-center justify-center border border-current/15">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 4.5h10.5A2.25 2.25 0 0 1 19.5 6.75v10.5A2.25 2.25 0 0 1 17.25 19.5H6.75A2.25 2.25 0 0 1 4.5 17.25V6.75A2.25 2.25 0 0 1 6.75 4.5Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9.75h7.5M8.25 13.5h5.25M8.25 7.5h7.5" />
                </svg>
            </span>
            Receipts
        </a>

        <a
            href="{{ route('invoices.settled') }}"
            class="{{ request()->routeIs('invoices.settled') ? 'border-sky-400/40 bg-sky-400/10 text-white' : 'border-transparent text-slate-300 hover:border-white/10 hover:bg-white/5 hover:text-white' }} flex items-center gap-3 border px-4 py-3 font-medium transition"
        >
            <span class="inline-flex h-9 w-9 items-center justify-center border border-current/15">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </span>
            Settled Invoices
        </a>

        <a
            href="{{ route('profile.edit') }}"
            class="{{ request()->routeIs('profile.*') ? 'border-sky-400/40 bg-sky-400/10 text-white' : 'border-transparent text-slate-300 hover:border-white/10 hover:bg-white/5 hover:text-white' }} flex items-center gap-3 border px-4 py-3 font-medium transition"
        >
            <span class="inline-flex h-9 w-9 items-center justify-center border border-current/15">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6.75a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 19.125a7.5 7.5 0 0 1 15 0" />
                </svg>
            </span>
            Account Settings
        </a>
    </nav>

    <div class="border-t border-white/10 px-6 py-5">
        <div class="mb-4">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-400">Current user</p>
            <p class="mt-2 text-base font-semibold text-white">{{ $user->name }}</p>
            <p class="text-sm text-slate-400">{{ $user->email }}</p>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button
                type="submit"
                class="inline-flex w-full items-center justify-center border border-white/10 bg-white/5 px-4 py-3 text-sm font-semibold text-white transition hover:bg-white/10"
            >
                Log out
            </button>
        </form>
    </div>
</aside>
