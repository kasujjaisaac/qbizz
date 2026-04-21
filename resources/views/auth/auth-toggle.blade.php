@php
    $currentTab = old('auth_mode', $active ?? 'login');
@endphp

<x-blank-layout>
    <div
        x-data="{
            tab: '{{ $currentTab }}',
            switchTab(nextTab) {
                this.tab = nextTab;
                window.history.replaceState({}, '', nextTab === 'login' ? '{{ route('login') }}' : '{{ route('register') }}');
            }
        }"
        class="auth-shell"
    >
        <div class="min-h-screen lg:grid lg:grid-cols-[465px_minmax(0,1fr)]">
            <aside class="auth-blue-panel hidden lg:block">
                <div class="auth-panel-inner auth-animate">
                    <div class="space-y-5">
                        <div class="auth-logo-card">
                            <img src="{{ asset('images/qbizz-mark-transparent.png') }}" alt="Qbizz logo" class="auth-logo-image">
                        </div>

                        <div class="auth-feature-list auth-feature-list--top hidden lg:grid">
                            <article class="auth-feature-card auth-animate">
                                <div class="auth-feature-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="h-5 w-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75h7.379a2.25 2.25 0 011.59.659l2.122 2.121a2.25 2.25 0 01.659 1.591V19.5A2.25 2.25 0 0117 21.75H7.5A2.25 2.25 0 015.25 19.5V6A2.25 2.25 0 017.5 3.75z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9.75h7.5M8.25 13.5h7.5M8.25 17.25h4.5" />
                                    </svg>
                                </div>

                                <div class="space-y-1">
                                    <h3 class="text-base font-semibold text-white">Create invoices</h3>
                                    <p class="text-sm leading-6 text-white">
                                        Prepare professional invoices and issue them quickly from one workflow.
                                    </p>
                                </div>
                            </article>

                            <article class="auth-feature-card auth-animate">
                                <div class="auth-feature-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="h-5 w-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 8.25h16.5M6.75 3.75h10.5A2.25 2.25 0 0119.5 6v12A2.25 2.25 0 0117.25 20.25H6.75A2.25 2.25 0 014.5 18V6A2.25 2.25 0 016.75 3.75z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25h2.25m4.5 0h2.25" />
                                    </svg>
                                </div>

                                <div class="space-y-1">
                                    <h3 class="text-base font-semibold text-white">Manage payments</h3>
                                    <p class="text-sm leading-6 text-white">
                                        Follow paid, partial, and overdue balances with a clear record trail.
                                    </p>
                                </div>
                            </article>

                            <article class="auth-feature-card auth-animate">
                                <div class="auth-feature-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="h-5 w-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6.75a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 19.125a7.5 7.5 0 0115 0" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.75 9.75h1.5m-11.25 0H3.75m15 3.75h1.5m-11.25 0H3.75" />
                                    </svg>
                                </div>

                                <div class="space-y-1">
                                    <h3 class="text-base font-semibold text-white">Track customers</h3>
                                    <p class="text-sm leading-6 text-white">
                                        Keep invoiced customer records organized and ready for follow-up.
                                    </p>
                                </div>
                            </article>
                        </div>

                        <div class="auth-panel-tag">Invoice &amp; Receipts Portal</div>
                    </div>
                </div>
            </aside>

            <section class="auth-grid-surface flex min-h-screen flex-col items-center justify-center gap-6 px-4 py-6 sm:px-8 sm:py-8 lg:min-h-0 lg:items-start lg:justify-start lg:gap-0 lg:px-10 lg:py-12 xl:px-12">
                <div class="auth-mobile-logo auth-animate lg:hidden">
                    <img src="{{ asset('images/qbizz-mark-transparent.png') }}" alt="Qbizz logo" class="auth-mobile-logo-image">
                </div>

                <div class="w-full max-w-none sm:max-w-[370px] lg:mr-auto">
                    <div class="auth-tab-rail auth-animate mb-3 grid grid-cols-2 gap-2 p-2">
                        <button
                            type="button"
                            @click="switchTab('login')"
                            :class="tab === 'login' ? 'is-active' : ''"
                            class="auth-tab"
                        >
                            Sign in
                        </button>

                        <button
                            type="button"
                            @click="switchTab('register')"
                            :class="tab === 'register' ? 'is-active' : ''"
                            class="auth-tab"
                        >
                            Sign up
                        </button>
                    </div>

                    <div class="auth-card auth-animate w-full max-w-none p-5 sm:p-6">
                        <div class="space-y-4">
                            <div class="space-y-1">
                                <p class="text-[0.7rem] font-semibold uppercase tracking-[0.32em] text-sky-700">Secure Access</p>
                                <h3 class="text-[1.85rem] font-semibold tracking-tight text-slate-950" x-text="tab === 'login' ? 'Sign In' : 'Sign Up'"></h3>
                                <p class="text-sm leading-6 text-slate-500" x-text="tab === 'login'
                                    ? 'Use your account to continue to Qbizz.'
                                    : 'Create your account to start managing billing.'"></p>
                            </div>

                            <x-auth-session-status
                                class="border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
                                :status="session('status')"
                            />

                            @if ($errors->any() && $currentTab === 'login')
                                <div class="border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                                    <p class="font-semibold text-rose-800">We could not sign you in.</p>
                                    <p class="mt-1 leading-6">{{ $errors->first('email') ?: 'Please check your credentials and try again.' }}</p>
                                </div>
                            @endif

                            @if ($errors->any() && $currentTab === 'register')
                                <div class="border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                                    <p class="font-semibold text-rose-800">Please fix the highlighted details before continuing.</p>
                                    <ul class="mt-2 space-y-1 leading-6">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>

                        <div x-show="tab === 'login'" x-transition.opacity.duration.250ms x-cloak class="mt-5">
                            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                                @csrf
                                <input type="hidden" name="auth_mode" value="login">

                            <div class="space-y-2">
                                <label for="login_email" class="auth-label">Email address</label>
                                <input
                                    id="login_email"
                                    type="email"
                                    name="email"
                                    value="{{ old('auth_mode') === 'login' ? old('email') : '' }}"
                                    required
                                    @if ($currentTab === 'login')
                                        autofocus
                                    @endif
                                    autocomplete="username"
                                    placeholder="name@company.com"
                                    class="auth-input"
                                >
                            </div>

                            <div class="space-y-2">
                                <label for="login_password" class="auth-label">Password</label>
                                <input
                                    id="login_password"
                                    type="password"
                                    name="password"
                                    required
                                    autocomplete="current-password"
                                    placeholder="Enter your password"
                                    class="auth-input"
                                >
                            </div>

                            <div class="flex flex-col gap-4 text-sm text-slate-600 sm:flex-row sm:items-center sm:justify-between">
                                <label for="remember_me" class="inline-flex items-center gap-3">
                                    <input
                                        id="remember_me"
                                        type="checkbox"
                                        name="remember"
                                        @checked(old('remember'))
                                        class="h-4 w-4 rounded-none border-slate-300 text-sky-700 focus:ring-sky-600"
                                    >
                                    <span>Keep me signed in</span>
                                </label>

                                @if (Route::has('password.request'))
                                    <a class="font-medium text-sky-700 transition hover:text-sky-900" href="{{ route('password.request') }}">
                                        Forgot your password?
                                    </a>
                                @endif
                            </div>

                            <button type="submit" class="auth-submit w-full">
                                Sign in to your account
                            </button>
                        </form>
                    </div>

                    <div x-show="tab === 'register'" x-transition.opacity.duration.250ms x-cloak class="mt-5">
                        <form method="POST" action="{{ route('register') }}" class="space-y-4">
                            @csrf
                            <input type="hidden" name="auth_mode" value="register">

                            <div class="space-y-2">
                                <label for="register_name" class="auth-label">Full name</label>
                                <input
                                    id="register_name"
                                    type="text"
                                    name="name"
                                    value="{{ old('auth_mode') === 'register' ? old('name') : '' }}"
                                    required
                                    autocomplete="name"
                                    placeholder="Enter your full name"
                                    class="auth-input"
                                >
                                @if ($currentTab === 'register')
                                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                @endif
                            </div>

                            <div class="space-y-2">
                                <label for="register_email" class="auth-label">Email address</label>
                                <input
                                    id="register_email"
                                    type="email"
                                    name="email"
                                    value="{{ old('auth_mode') === 'register' ? old('email') : '' }}"
                                    required
                                    autocomplete="username"
                                    placeholder="name@company.com"
                                    class="auth-input"
                                >
                                @if ($currentTab === 'register')
                                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                @endif
                            </div>

                            <div class="space-y-2">
                                <label for="register_invite_code" class="auth-label">Team invite code</label>
                                <input
                                    id="register_invite_code"
                                    type="text"
                                    name="invite_code"
                                    value="{{ old('auth_mode') === 'register' ? old('invite_code') : '' }}"
                                    autocomplete="off"
                                    placeholder="Optional: join an existing company"
                                    class="auth-input"
                                >
                                <p class="text-xs leading-5 text-slate-500">Leave this blank to create your own company workspace after signup.</p>
                                @if ($currentTab === 'register')
                                    <x-input-error :messages="$errors->get('invite_code')" class="mt-2" />
                                @endif
                            </div>

                            <div class="grid gap-4">
                                <div class="space-y-2">
                                    <label for="register_password" class="auth-label">Password</label>
                                    <input
                                        id="register_password"
                                        type="password"
                                        name="password"
                                        required
                                        autocomplete="new-password"
                                        placeholder="Create a password"
                                        class="auth-input"
                                    >
                                    @if ($currentTab === 'register')
                                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                    @endif
                                </div>

                                <div class="space-y-2">
                                    <label for="register_password_confirmation" class="auth-label">Confirm password</label>
                                    <input
                                        id="register_password_confirmation"
                                        type="password"
                                        name="password_confirmation"
                                        required
                                        autocomplete="new-password"
                                        placeholder="Repeat password"
                                        class="auth-input"
                                    >
                                    @if ($currentTab === 'register')
                                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                                    @endif
                                </div>
                            </div>

                            <button type="submit" class="auth-submit w-full">
                                Create your account
                            </button>
                        </form>
                    </div>
                </div>
                </div>
            </section>
        </div>
    </div>
</x-blank-layout>
