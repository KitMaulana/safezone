<div x-data="sszScanner()" x-init="init()" class="space-y-4">
    @section('header', 'Scan QR')

    {{-- Pengaturan scan --}}
    <x-ui.card padding="p-4">
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="gate" class="mb-1.5 block text-sm font-medium text-slate-700">Gerbang</label>
                <select id="gate" x-model="gateId" x-on:change="rememberGate()"
                        class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    @foreach ($gates as $gate)
                        <option value="{{ $gate->id }}">{{ $gate->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="mode" class="mb-1.5 block text-sm font-medium text-slate-700">Mode</label>
                <select id="mode" x-model="mode"
                        class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    <option value="auto">Otomatis</option>
                    <option value="in">Selalu Cek In</option>
                    <option value="out">Selalu Cek Out</option>
                </select>
            </div>
        </div>

        <div class="mt-3 flex flex-wrap items-center gap-2">
            <button type="button" x-on:click="toggleTorch()" x-show="torchSupported"
                    class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                <span x-text="torchOn ? 'Matikan senter' : 'Nyalakan senter'"></span>
            </button>

            <template x-if="pending.length">
                <span class="inline-flex items-center gap-1 rounded-full bg-warning-50 px-3 py-1 text-xs font-semibold text-warning-700">
                    <span x-text="pending.length"></span> scan menunggu terkirim
                </span>
            </template>

            <template x-if="pending.length">
                <button type="button" x-on:click="flushQueue()" class="text-xs font-semibold text-brand-600 underline">Kirim sekarang</button>
            </template>
        </div>
    </x-ui.card>

    {{-- Kamera --}}
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-slate-900">
        <div id="reader" class="w-full"></div>
    </div>

    <p class="text-center text-xs text-slate-500">
        Arahkan kamera ke QR pada stiker Kartu Masuk. Hasil muncul otomatis.
    </p>

    <div class="flex justify-center">
        <x-ui.button variant="secondary" x-on:click="restart()" x-show="!scanning">
            <x-icon.camera class="h-5 w-5" /> Mulai ulang kamera
        </x-ui.button>
    </div>

    {{-- Layar hasil penuh --}}
    <template x-if="result">
        <div class="fixed inset-0 z-50 flex flex-col items-center justify-center px-6 text-center"
             :class="{
                'bg-success-500 text-white': result.color === 'green',
                'bg-danger-500 text-white': result.color === 'red',
                'bg-warning-500 text-white': result.color === 'yellow',
                'bg-slate-600 text-white': result.color === 'gray',
             }"
             x-on:click="closeResult()">

            <template x-if="result.student && result.student.photo_url">
                <img :src="result.student.photo_url" alt="" class="mb-4 h-28 w-28 rounded-full object-cover ring-4 ring-white/70">
            </template>

            <p class="text-3xl font-extrabold leading-tight" x-text="result.message"></p>

            <template x-if="result.student">
                <div class="mt-3">
                    <p class="text-xl font-bold" x-text="result.student.name"></p>
                    <p class="text-base opacity-90" x-text="result.student.class_room"></p>
                </div>
            </template>

            <template x-if="result.vehicle">
                <p class="ssz-plate mt-3 rounded-lg bg-white/20 px-4 py-2 text-2xl font-extrabold" x-text="result.vehicle.plate"></p>
            </template>

            <template x-if="result.reason">
                <p class="mt-4 max-w-sm text-base opacity-95" x-text="result.reason"></p>
            </template>

            <template x-if="result.status === 'denied_blocked'">
                <p class="mt-3 text-sm font-semibold uppercase tracking-wide opacity-90">Tidak dicatat sebagai cek in</p>
            </template>

            <button type="button" class="mt-8 rounded-xl bg-white/20 px-6 py-3 text-base font-bold" x-on:click.stop="closeResult()">
                Scan lagi
            </button>
        </div>
    </template>
</div>

@push('head')
    @vite('resources/js/scanner.js')
@endpush

@push('scripts')
<script>
    function sszScanner() {
        return {
            html5Qr: null,
            scanning: false,
            busy: false,
            result: null,
            mode: 'auto',
            gateId: localStorage.getItem('ssz.gate') || @json($gates->first()?->id),
            torchSupported: false,
            torchOn: false,
            pending: [],
            closeTimer: null,

            async init() {
                await this.loadQueue();
                this.start();

                window.addEventListener('online', () => this.flushQueue());

                if (navigator.onLine) {
                    this.flushQueue();
                }
            },

            rememberGate() {
                localStorage.setItem('ssz.gate', this.gateId);
            },

            async start() {
                // Tunggu bundel scanner selesai dimuat (resources/js/scanner.js).
                if (! window.Html5Qrcode) {
                    await new Promise((resolve) => {
                        window.addEventListener('html5qrcode-ready', resolve, { once: true });
                        setTimeout(resolve, 5000);
                    });
                }

                if (! window.Html5Qrcode) {
                    this.scanning = false;
                    alert('Pustaka pemindai gagal dimuat. Muat ulang halaman.');

                    return;
                }

                this.html5Qr = new window.Html5Qrcode('reader', { verbose: false });

                try {
                    await this.html5Qr.start(
                        { facingMode: 'environment' },
                        { fps: 10, qrbox: { width: 250, height: 250 } },
                        (text) => this.onScan(text),
                        () => {}
                    );

                    this.scanning = true;

                    const capabilities = this.html5Qr.getRunningTrackCapabilities?.() || {};
                    this.torchSupported = 'torch' in capabilities;
                } catch (e) {
                    this.scanning = false;
                    alert('Tidak bisa membuka kamera. Pastikan izin kamera diberikan dan halaman dibuka lewat HTTPS.');
                }
            },

            async restart() {
                try { await this.html5Qr?.stop(); } catch (e) {}
                this.start();
            },

            async toggleTorch() {
                try {
                    this.torchOn = !this.torchOn;
                    await this.html5Qr.applyVideoConstraints({ advanced: [{ torch: this.torchOn }] });
                } catch (e) {
                    this.torchSupported = false;
                }
            },

            /** Ambil token dari URL QR: https://domain/q/{token} */
            extractToken(text) {
                const match = String(text).match(/\/q\/([A-Za-z0-9]{32})/);
                return match ? match[1] : String(text).trim();
            },

            async onScan(text) {
                if (this.busy || this.result) return;

                this.busy = true;

                const token = this.extractToken(text);
                const payload = {
                    token,
                    mode: this.mode,
                    gate_id: this.gateId,
                };

                try {
                    const result = await this.post(payload);
                    this.showResult(result);
                } catch (e) {
                    // Offline: masukkan ke antrean IndexedDB agar terkirim saat online.
                    await this.enqueue({
                        ...payload,
                        offline_id: crypto.randomUUID(),
                        client_scanned_at: new Date().toISOString(),
                    });

                    this.showResult({
                        color: 'gray',
                        status: 'queued',
                        message: 'MASUK ANTREAN',
                        reason: 'Tidak ada koneksi. Scan akan dikirim otomatis saat online.',
                    });
                } finally {
                    this.busy = false;
                }
            },

            async post(payload) {
                const response = await fetch(@json(route('api.scan')), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    },
                    body: JSON.stringify(payload),
                });

                if (!response.ok && response.status >= 500) {
                    throw new Error('server');
                }

                return await response.json();
            },

            showResult(result) {
                this.result = result;

                if (result.color === 'green') {
                    window.SSZ.vibrate(90);
                    window.SSZ.beep(980, 130);
                } else if (result.color === 'red') {
                    window.SSZ.vibrate([90, 70, 90, 70, 160]);
                    window.SSZ.beep(220, 420, 'square');
                } else if (result.color === 'yellow') {
                    window.SSZ.vibrate([70, 70, 70]);
                    window.SSZ.beep(520, 260, 'triangle');
                } else {
                    window.SSZ.vibrate(50);
                    window.SSZ.beep(400, 120);
                }

                clearTimeout(this.closeTimer);
                this.closeTimer = setTimeout(() => this.closeResult(), 3000);
            },

            closeResult() {
                clearTimeout(this.closeTimer);
                this.result = null;
            },

            /* ---------- Antrean offline (IndexedDB) ---------- */

            db() {
                return new Promise((resolve, reject) => {
                    const request = indexedDB.open('ssz', 1);

                    request.onupgradeneeded = () => {
                        request.result.createObjectStore('pendingScans', { keyPath: 'offline_id' });
                    };

                    request.onsuccess = () => resolve(request.result);
                    request.onerror = () => reject(request.error);
                });
            },

            async enqueue(item) {
                const db = await this.db();
                const tx = db.transaction('pendingScans', 'readwrite');
                tx.objectStore('pendingScans').put(item);
                await new Promise((resolve) => (tx.oncomplete = resolve));
                await this.loadQueue();
            },

            async dequeue(offlineId) {
                const db = await this.db();
                const tx = db.transaction('pendingScans', 'readwrite');
                tx.objectStore('pendingScans').delete(offlineId);
                await new Promise((resolve) => (tx.oncomplete = resolve));
            },

            async loadQueue() {
                try {
                    const db = await this.db();
                    const tx = db.transaction('pendingScans', 'readonly');
                    const request = tx.objectStore('pendingScans').getAll();

                    this.pending = await new Promise((resolve) => {
                        request.onsuccess = () => resolve(request.result || []);
                        request.onerror = () => resolve([]);
                    });
                } catch (e) {
                    this.pending = [];
                }
            },

            async flushQueue() {
                await this.loadQueue();

                for (const item of this.pending) {
                    try {
                        await this.post(item);
                        await this.dequeue(item.offline_id);
                    } catch (e) {
                        break; // masih offline, coba lagi nanti
                    }
                }

                await this.loadQueue();
            },
        };
    }
</script>
@endpush
