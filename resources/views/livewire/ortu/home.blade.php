<div class="space-y-4" wire:poll.15s>
    @section('header', 'Beranda')

    {{-- Pemilih anak --}}
    @if ($children->count() > 1)
        <div class="ssz-scroll-hide -mx-1 overflow-x-auto">
            <div class="flex gap-2 px-1">
                @foreach ($children as $child)
                    <button type="button" wire:click="selectChild({{ $child->id }})"
                            class="whitespace-nowrap rounded-lg px-4 py-2 text-sm font-medium transition {{ $selectedStudentId === $child->id ? 'bg-brand-600 text-white' : 'bg-white text-slate-600' }}">
                        {{ $child->name }}
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    @if ($student)
        {{-- Kartu status --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-start gap-4">
                @if ($student->photo_path)
                    <img src="{{ route('media', ['path' => $student->photo_path]) }}" alt="Foto {{ $student->name }}" class="h-16 w-16 rounded-xl object-cover">
                @else
                    <x-ui.avatar :name="$student->name" size="lg" class="rounded-xl" />
                @endif

                <div class="min-w-0 flex-1">
                    <p class="truncate text-base font-bold text-slate-900">{{ $student->name }}</p>
                    <p class="text-sm text-slate-500">{{ $student->class_room }}</p>
                    @if ($vehicle)
                        <p class="ssz-plate mt-1 font-mono text-sm font-semibold text-slate-700">{{ $vehicle->formatted_plate }}</p>
                    @endif
                </div>
            </div>

            <div class="mt-4 rounded-lg p-4 text-center
                {{ $statusColor === 'success' ? 'bg-success-50 text-success-700' : '' }}
                {{ $statusColor === 'warning' ? 'bg-warning-50 text-warning-700' : '' }}
                {{ $statusColor === 'slate' ? 'bg-slate-100 text-slate-700' : '' }}">
                <p class="text-lg font-bold">{{ $statusText }}</p>
                @if ($checkOut && $checkOut->is_early_leave)
                    <p class="mt-1 text-sm font-medium">Keluar lebih awal dari jadwal.</p>
                @endif
            </div>

            <div class="mt-3 flex items-center justify-between gap-3 text-sm">
                <span class="text-slate-500">Status stiker</span>
                @if ($permit)
                    <x-ui.badge :color="$permit->status->badgeColor()">{{ $permit->status->label() }}</x-ui.badge>
                @else
                    <x-ui.badge color="slate">Belum ada stiker</x-ui.badge>
                @endif
            </div>
        </div>

        @if ($student->status->value === 'blocked')
            <x-ui.alert type="danger" title="Kartu masuk ditangguhkan">
                {{ $student->blocked_reason }}
            </x-ui.alert>
        @endif

        {{-- Aktivasi notifikasi --}}
        <x-ui.card title="Notifikasi">
            <div x-data="sszPush()" x-init="check()" class="space-y-3">
                <p class="text-sm text-slate-600" x-text="statusText"></p>

                <template x-if="canSubscribe">
                    <x-ui.button x-on:click="subscribe()" class="w-full">
                        <x-icon.bell class="h-5 w-5" /> Aktifkan Notifikasi
                    </x-ui.button>
                </template>

                <template x-if="subscribed">
                    <x-ui.button variant="secondary" x-on:click="unsubscribe()" class="w-full">
                        Matikan notifikasi di perangkat ini
                    </x-ui.button>
                </template>
            </div>
        </x-ui.card>

        <div class="grid grid-cols-2 gap-2">
            <x-ui.stat label="Cek in" :value="$checkIn?->scanned_at->timezone('Asia/Jakarta')->format('H.i') ?? '—'" icon="check-circle" color="success" />
            <x-ui.stat label="Cek out" :value="$checkOut?->scanned_at->timezone('Asia/Jakarta')->format('H.i') ?? '—'" icon="logout" color="brand" />
        </div>

        <x-ui.button variant="secondary" :href="route('ortu.history', ['student' => $student->id])" class="w-full">
            <x-icon.clock class="h-5 w-5" /> Lihat riwayat lengkap
        </x-ui.button>
    @endif
</div>

@push('scripts')
<script>
    function sszPush() {
        return {
            statusText: 'Memeriksa status notifikasi…',
            canSubscribe: false,
            subscribed: false,

            async check() {
                if (! ('serviceWorker' in navigator) || ! ('PushManager' in window)) {
                    this.statusText = 'Peramban ini belum mendukung notifikasi push.';

                    return;
                }

                if (Notification.permission === 'denied') {
                    this.statusText = 'Notifikasi diblokir di pengaturan peramban. Aktifkan lewat pengaturan situs.';

                    return;
                }

                const registration = await navigator.serviceWorker.ready;
                const subscription = await registration.pushManager.getSubscription();

                if (subscription) {
                    this.subscribed = true;
                    this.statusText = 'Notifikasi aktif di perangkat ini.';
                } else {
                    this.canSubscribe = true;
                    this.statusText = 'Aktifkan agar Anda menerima pemberitahuan saat anak cek in dan cek out.';
                }
            },

            urlBase64ToUint8Array(base64String) {
                const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
                const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
                const raw = window.atob(base64);

                return Uint8Array.from([...raw].map((c) => c.charCodeAt(0)));
            },

            async subscribe() {
                const vapid = @json(config('webpush.vapid.public_key'));

                if (! vapid) {
                    this.statusText = 'Kunci VAPID belum diatur di server. Hubungi admin sekolah.';

                    return;
                }

                const permission = await Notification.requestPermission();

                if (permission !== 'granted') {
                    this.statusText = 'Izin notifikasi tidak diberikan.';

                    return;
                }

                const registration = await navigator.serviceWorker.ready;

                const subscription = await registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: this.urlBase64ToUint8Array(vapid),
                });

                await fetch(@json(route('push.subscribe')), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    },
                    body: JSON.stringify(subscription.toJSON()),
                });

                this.canSubscribe = false;
                this.subscribed = true;
                this.statusText = 'Notifikasi aktif di perangkat ini.';
            },

            async unsubscribe() {
                const registration = await navigator.serviceWorker.ready;
                const subscription = await registration.pushManager.getSubscription();

                if (subscription) {
                    await fetch(@json(route('push.unsubscribe')), {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        },
                        body: JSON.stringify({ endpoint: subscription.endpoint }),
                    });

                    await subscription.unsubscribe();
                }

                this.subscribed = false;
                this.canSubscribe = true;
                this.statusText = 'Notifikasi dimatikan di perangkat ini.';
            },
        };
    }
</script>
@endpush
