<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-slate-500">Onboarding</p>
            <h1 class="text-2xl font-semibold text-slate-950">Business Profile Setup</h1>
        </div>
    </x-slot>

    <div class="grid gap-6 xl:grid-cols-[360px_minmax(0,1fr)] xl:items-start">
        <aside class="order-2 space-y-6 xl:order-1">
            <div class="border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Completion status</p>
                <h2 class="mt-3 text-2xl font-semibold text-slate-950">{{ $completionPercentage }}% complete</h2>
                <p class="mt-2 text-sm leading-7 text-slate-600">
                    The details here are used on printed invoices and receipts, so this setup needs to be complete before the dashboard and billing tools open up.
                </p>

                <div class="mt-5 h-3 bg-slate-100">
                    <div class="h-full bg-sky-600" style="width: {{ $completionPercentage }}%"></div>
                </div>

                @if (empty($missingFields))
                    <div class="mt-5 border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        Your business profile is complete and ready to appear on invoices.
                    </div>
                @else
                    <div class="mt-5 border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                        <p class="font-semibold">Still needed for full setup:</p>
                        <ul class="mt-3 space-y-2">
                            @foreach ($missingFields as $field)
                                <li>{{ $field }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <div class="border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">What appears on documents</p>
                <div class="mt-4 space-y-3 text-sm leading-7 text-slate-600">
                    <p>Your logo, business name, email, telephone, address, stamp, and signature can appear on printable invoices and receipts.</p>
                    <p>Complete these details once and the system will reuse them across every document you issue.</p>
                </div>
            </div>

            <div class="border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Team workspace</p>
                @if ($businessProfile->exists)
                    <h2 class="mt-3 text-lg font-semibold text-slate-950">Invite teammates into this company</h2>
                    <p class="mt-2 text-sm leading-7 text-slate-600">
                        New staff accounts can join this business during signup with the invite code below. Everyone in the workspace shares the same quotations, invoices, and receipts.
                    </p>

                    <div class="mt-5 border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Invite code</p>
                        <p class="mt-2 break-all text-xl font-semibold tracking-[0.12em] text-slate-950">{{ $businessProfile->team_invite_code }}</p>
                    </div>

                    <div class="mt-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Current team</p>
                        <div class="mt-3 space-y-3 text-sm text-slate-600">
                            @foreach ($teamMembers as $member)
                                <div class="border border-slate-200 px-4 py-3">
                                    <p class="font-semibold text-slate-900">{{ $member->name }}</p>
                                    <p class="mt-1">{{ $member->email }}</p>
                                    @if ($member->id === $businessProfile->user_id)
                                        <p class="mt-2 text-xs font-semibold uppercase tracking-[0.24em] text-sky-700">Workspace owner</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <h2 class="mt-3 text-lg font-semibold text-slate-950">Create the company first</h2>
                    <p class="mt-2 text-sm leading-7 text-slate-600">
                        Save the business profile once and Qbizz will generate an invite code you can share with teammates at signup.
                    </p>
                @endif
            </div>
        </aside>

        <section class="order-1 border border-slate-200 bg-white p-4 shadow-sm sm:p-8 xl:order-2">
            <form method="POST" action="{{ route('business-profile.update') }}" enctype="multipart/form-data" class="space-y-8">
                @csrf
                @method('PUT')

                <div class="grid gap-8 lg:grid-cols-2">
                    <div class="space-y-6">
                        <div>
                            <label for="business_name" class="block text-sm font-semibold text-slate-900">Business name</label>
                            <input id="business_name" name="business_name" type="text" value="{{ old('business_name', $businessProfile->business_name) }}" class="mt-2 block w-full rounded-none border-slate-300 text-sm shadow-sm focus:border-sky-600 focus:ring-sky-600" required>
                            <x-input-error :messages="$errors->get('business_name')" class="mt-2" />
                        </div>

                        <div>
                            <label for="contact_email" class="block text-sm font-semibold text-slate-900">Business email</label>
                            <input id="contact_email" name="contact_email" type="email" value="{{ old('contact_email', $businessProfile->contact_email) }}" class="mt-2 block w-full rounded-none border-slate-300 text-sm shadow-sm focus:border-sky-600 focus:ring-sky-600">
                            <x-input-error :messages="$errors->get('contact_email')" class="mt-2" />
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-semibold text-slate-900">Telephone</label>
                            <input id="phone" name="phone" type="text" value="{{ old('phone', $businessProfile->phone) }}" class="mt-2 block w-full rounded-none border-slate-300 text-sm shadow-sm focus:border-sky-600 focus:ring-sky-600">
                            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                        </div>

                        <div>
                            <label for="website" class="block text-sm font-semibold text-slate-900">Website</label>
                            <input id="website" name="website" type="url" value="{{ old('website', $businessProfile->website) }}" class="mt-2 block w-full rounded-none border-slate-300 text-sm shadow-sm focus:border-sky-600 focus:ring-sky-600">
                            <x-input-error :messages="$errors->get('website')" class="mt-2" />
                        </div>

                        <div>
                            <label for="tax_id" class="block text-sm font-semibold text-slate-900">Tax ID / Registration number</label>
                            <input id="tax_id" name="tax_id" type="text" value="{{ old('tax_id', $businessProfile->tax_id) }}" class="mt-2 block w-full rounded-none border-slate-300 text-sm shadow-sm focus:border-sky-600 focus:ring-sky-600">
                            <x-input-error :messages="$errors->get('tax_id')" class="mt-2" />
                        </div>

                        <div>
                            <label for="issuer_title" class="block text-sm font-semibold text-slate-900">Issuer title</label>
                            <input id="issuer_title" name="issuer_title" type="text" value="{{ old('issuer_title', $businessProfile->issuer_title) }}" class="mt-2 block w-full rounded-none border-slate-300 text-sm shadow-sm focus:border-sky-600 focus:ring-sky-600" placeholder="Owner, Manager, Director...">
                            <x-input-error :messages="$errors->get('issuer_title')" class="mt-2" />
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label for="logo" class="block text-sm font-semibold text-slate-900">Business logo</label>
                            <input id="logo" name="logo" type="file" accept="image/*" class="mt-2 block w-full rounded-none border border-slate-300 bg-white text-sm text-slate-700 file:mr-4 file:rounded-none file:border-0 file:bg-slate-900 file:px-4 file:py-3 file:font-semibold file:text-white">
                            <x-input-error :messages="$errors->get('logo')" class="mt-2" />

                            @if ($businessProfile->logo_path)
                                <div class="mt-4 border border-slate-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Current logo</p>
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($businessProfile->logo_path) }}" alt="Business logo" class="mt-3 h-20 w-20 object-contain">
                                </div>
                            @endif
                        </div>

                        <div>
                            <label for="address_line_1" class="block text-sm font-semibold text-slate-900">Address line 1</label>
                            <input id="address_line_1" name="address_line_1" type="text" value="{{ old('address_line_1', $businessProfile->address_line_1) }}" class="mt-2 block w-full rounded-none border-slate-300 text-sm shadow-sm focus:border-sky-600 focus:ring-sky-600">
                            <x-input-error :messages="$errors->get('address_line_1')" class="mt-2" />
                        </div>

                        <div>
                            <label for="address_line_2" class="block text-sm font-semibold text-slate-900">Address line 2</label>
                            <input id="address_line_2" name="address_line_2" type="text" value="{{ old('address_line_2', $businessProfile->address_line_2) }}" class="mt-2 block w-full rounded-none border-slate-300 text-sm shadow-sm focus:border-sky-600 focus:ring-sky-600">
                            <x-input-error :messages="$errors->get('address_line_2')" class="mt-2" />
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <label for="city" class="block text-sm font-semibold text-slate-900">City</label>
                                <input id="city" name="city" type="text" value="{{ old('city', $businessProfile->city) }}" class="mt-2 block w-full rounded-none border-slate-300 text-sm shadow-sm focus:border-sky-600 focus:ring-sky-600">
                                <x-input-error :messages="$errors->get('city')" class="mt-2" />
                            </div>

                            <div>
                                <label for="state" class="block text-sm font-semibold text-slate-900">State / Region</label>
                                <input id="state" name="state" type="text" value="{{ old('state', $businessProfile->state) }}" class="mt-2 block w-full rounded-none border-slate-300 text-sm shadow-sm focus:border-sky-600 focus:ring-sky-600">
                                <x-input-error :messages="$errors->get('state')" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <label for="postal_code" class="block text-sm font-semibold text-slate-900">Postal code</label>
                                <input id="postal_code" name="postal_code" type="text" value="{{ old('postal_code', $businessProfile->postal_code) }}" class="mt-2 block w-full rounded-none border-slate-300 text-sm shadow-sm focus:border-sky-600 focus:ring-sky-600">
                                <x-input-error :messages="$errors->get('postal_code')" class="mt-2" />
                            </div>

                            <div>
                                <label for="country" class="block text-sm font-semibold text-slate-900">Country</label>
                                <input id="country" name="country" type="text" value="{{ old('country', $businessProfile->country) }}" class="mt-2 block w-full rounded-none border-slate-300 text-sm shadow-sm focus:border-sky-600 focus:ring-sky-600">
                                <x-input-error :messages="$errors->get('country')" class="mt-2" />
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    x-data="signatureField(@js(old('signature_data') ?: ($businessProfile->signature_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($businessProfile->signature_path) : null)))"
                    class="border-t border-slate-200 pt-8"
                >
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">Business owner signature</p>
                            <p class="mt-1 text-sm leading-6 text-slate-500">Draw the signature that should appear on printed receipts and invoices.</p>
                        </div>

                        <div class="flex gap-3">
                            <button type="button" @click="clearPad()" class="inline-flex items-center justify-center rounded-none border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">
                                Clear signature
                            </button>
                        </div>
                    </div>

                    <div class="mt-5 border border-slate-200 bg-slate-50 p-4">
                        <canvas
                            x-ref="canvas"
                            width="700"
                            height="220"
                            class="block w-full bg-white"
                            style="touch-action: none;"
                            @pointerdown.prevent="start($event)"
                            @pointermove.prevent="move($event)"
                            @pointerup.prevent="end()"
                            @pointerleave.prevent="end()"
                        ></canvas>
                    </div>

                    <input type="hidden" name="signature_data" x-ref="signatureData" value="{{ old('signature_data') }}">
                    <input type="hidden" name="clear_signature" :value="cleared ? 1 : 0">
                    <x-input-error :messages="$errors->get('signature_data')" class="mt-2" />
                </div>

                <div class="flex flex-col gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-slate-500">Save your progress any time. The dashboard unlocks automatically once setup reaches 100%.</p>
                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-none border border-slate-900 bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 sm:w-auto">
                        Save business profile
                    </button>
                </div>
            </form>
        </section>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('signatureField', (initialImage = null) => ({
                drawing: false,
                cleared: false,
                initialImage,
                init() {
                    this.context = this.$refs.canvas.getContext('2d');
                    this.context.lineWidth = 3;
                    this.context.lineCap = 'round';
                    this.context.lineJoin = 'round';
                    this.context.strokeStyle = '#0f172a';

                    if (this.initialImage) {
                        this.loadImage(this.initialImage);
                    }
                },
                point(event) {
                    const rect = this.$refs.canvas.getBoundingClientRect();

                    return {
                        x: (event.clientX - rect.left) * (this.$refs.canvas.width / rect.width),
                        y: (event.clientY - rect.top) * (this.$refs.canvas.height / rect.height),
                    };
                },
                start(event) {
                    this.drawing = true;
                    const point = this.point(event);
                    this.context.beginPath();
                    this.context.moveTo(point.x, point.y);
                },
                move(event) {
                    if (! this.drawing) {
                        return;
                    }

                    const point = this.point(event);
                    this.context.lineTo(point.x, point.y);
                    this.context.stroke();
                    this.store();
                },
                end() {
                    if (! this.drawing) {
                        return;
                    }

                    this.drawing = false;
                    this.context.beginPath();
                    this.store();
                },
                clearPad() {
                    this.context.clearRect(0, 0, this.$refs.canvas.width, this.$refs.canvas.height);
                    this.cleared = true;
                    this.$refs.signatureData.value = '';
                },
                store() {
                    this.cleared = false;
                    this.$refs.signatureData.value = this.$refs.canvas.toDataURL('image/png');
                },
                loadImage(source) {
                    const image = new Image();
                    image.onload = () => {
                        this.context.clearRect(0, 0, this.$refs.canvas.width, this.$refs.canvas.height);

                        const maxWidth = this.$refs.canvas.width - 40;
                        const maxHeight = this.$refs.canvas.height - 40;
                        const ratio = Math.min(maxWidth / image.width, maxHeight / image.height, 1);
                        const drawWidth = image.width * ratio;
                        const drawHeight = image.height * ratio;
                        const x = (this.$refs.canvas.width - drawWidth) / 2;
                        const y = (this.$refs.canvas.height - drawHeight) / 2;

                        this.context.drawImage(image, x, y, drawWidth, drawHeight);
                        this.store();
                    };
                    image.src = source;
                },
            }));
        });
    </script>
</x-app-layout>
