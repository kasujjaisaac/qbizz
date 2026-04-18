<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Qbizz') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=poppins:300,400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    @php
        $user = Auth::user();
        $nameParts = preg_split('/\s+/', trim($user->name));
        $firstName = $nameParts[0] ?? $user->name;
        $topbarNotifications = $topbarNotifications ?? [];
    @endphp
    <body class="bg-slate-100 font-sans antialiased text-slate-900">
        <div
            x-data="{
                sidebarOpen: false,
                userFirstName: @js($firstName),
                notificationsOpen: false,
                greetingLabel: 'Welcome',
                init() {
                    this.refreshTopbar();
                    setInterval(() => this.refreshTopbar(), 1000);
                },
                refreshTopbar() {
                    const now = new Date();
                    const hour = now.getHours();

                    this.greetingLabel = hour < 12
                        ? 'Good morning'
                        : hour < 17
                            ? 'Good afternoon'
                            : 'Good evening';
                },
            }"
            class="min-h-screen bg-slate-100"
            :class="{ 'h-screen overflow-hidden lg:h-auto lg:overflow-visible': sidebarOpen }"
        >
            <div
                x-cloak
                x-show="sidebarOpen"
                x-transition.opacity
                @click="sidebarOpen = false"
                class="fixed inset-0 z-30 bg-slate-950/45 lg:hidden"
            ></div>

            @include('layouts.navigation')

            <div class="min-w-0 lg:pl-72">
                <header class="sticky top-0 z-20 border-b border-blue-950/25 bg-blue-700 shadow-[0_10px_30px_-18px_rgba(30,64,175,0.75)]">
                    <div class="relative overflow-hidden px-3 py-3 sm:px-6 lg:px-8">
                        <div class="absolute inset-0 bg-[linear-gradient(135deg,rgba(255,255,255,0.12),transparent_38%,transparent_62%,rgba(255,255,255,0.08))]"></div>
                        <div class="absolute inset-x-0 top-0 h-px bg-white/20"></div>
                        <div class="relative">
                            <div class="flex min-w-0 items-center gap-2 sm:gap-3">
                                <button
                                    type="button"
                                    @click="sidebarOpen = true"
                                    class="inline-flex h-11 w-11 shrink-0 items-center justify-center bg-blue-800/30 text-white shadow-sm backdrop-blur-sm transition hover:bg-blue-800/45 lg:hidden"
                                >
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" />
                                    </svg>
                                </button>

                                <div class="flex h-10 min-w-0 max-w-[9.75rem] shrink items-center bg-blue-800/22 px-3 shadow-sm backdrop-blur-sm sm:max-w-[15rem] sm:px-4">
                                    <p
                                        class="truncate text-sm font-semibold tracking-[0.01em] text-white"
                                        x-text="userFirstName ? `${greetingLabel}, ${userFirstName}` : greetingLabel"
                                    ></p>
                                </div>

                                <div class="relative ml-auto shrink-0" @click.away="notificationsOpen = false">
                                    <button
                                        type="button"
                                        @click="notificationsOpen = !notificationsOpen"
                                        class="relative inline-flex h-10 w-10 shrink-0 items-center justify-center border border-white/70 bg-transparent text-white transition hover:bg-white/10"
                                        aria-label="Open alerts"
                                    >
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.821 23.821 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9a6 6 0 1 0-12 0v.75a8.967 8.967 0 0 1-2.312 6.022 23.821 23.821 0 0 0 5.454 1.31m5.715 0a24.255 24.255 0 0 1-5.715 0m5.715 0a3 3 0 1 1-5.715 0" />
                                        </svg>
                                    </button>

                                    <div
                                        x-cloak
                                        x-show="notificationsOpen"
                                        x-transition.opacity.duration.150ms
                                        class="absolute right-0 top-full z-30 mt-2 w-72 max-w-[calc(100vw-1.5rem)] overflow-hidden border border-blue-400/50 bg-blue-900 shadow-xl"
                                    >
                                        <div class="border-b border-white/10 px-4 py-3">
                                            <div class="flex items-center gap-2">
                                                <svg class="h-4 w-4 text-white/75" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.821 23.821 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9a6 6 0 1 0-12 0v.75a8.967 8.967 0 0 1-2.312 6.022 23.821 23.821 0 0 0 5.454 1.31m5.715 0a24.255 24.255 0 0 1-5.715 0m5.715 0a3 3 0 1 1-5.715 0" />
                                                </svg>
                                                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-white/75">Alerts</p>
                                            </div>
                                        </div>

                                        <div class="divide-y divide-white/10">
                                            @foreach ($topbarNotifications as $notification)
                                                <div class="px-4 py-3">
                                                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] {{ $notification['tone'] === 'rose' ? 'text-rose-200' : ($notification['tone'] === 'amber' ? 'text-amber-200' : ($notification['tone'] === 'violet' ? 'text-violet-200' : 'text-emerald-200')) }}">
                                                        {{ $notification['label'] }}
                                                    </p>
                                                    <p class="mt-1 text-sm font-medium text-white/90">{{ $notification['value'] }}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </header>

                <main class="px-3 py-4 sm:px-6 sm:py-6 lg:px-8">
                    @if (session('status'))
                        <div class="mb-6 border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                            {{ session('status') }}
                        </div>
                    @endif

                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
