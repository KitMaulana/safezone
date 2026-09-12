{{-- Banner "Pasang aplikasi" kustom: menangkap beforeinstallprompt, muncul maksimal sekali per 7 hari. --}}
<div
    x-data="sszInstallBanner()"
    x-init="init()"
    x-show="visible"
    x-cloak
    x-transition
    class="fixed inset-x-0 bottom-20 z-40 px-4 lg:bottom-6"
>
    <div class="mx-auto flex max-w-md items-center gap-3 rounded-xl border border-brand-200 bg-white p-3 shadow-lg">
        <img src="/icons/icon-192.png" alt="" class="h-10 w-10 shrink-0 rounded-lg">
        <div class="min-w-0 flex-1">
            <p class="text-sm font-semibold text-slate-900">Pasang aplikasi</p>
            <p class="text-xs text-slate-500">Tambahkan SSZ Ciruas ke layar utama HP Anda.</p>
        </div>
        <button type="button" x-on:click="install()" class="shrink-0 rounded-lg bg-brand-600 px-3 py-2 text-xs font-bold text-white">Pasang</button>
        <button type="button" x-on:click="dismiss()" class="shrink-0 rounded-lg p-2 text-slate-400" aria-label="Tutup">
            <x-icon.x class="h-4 w-4" />
        </button>
    </div>
</div>

@push('scripts')
<script>
    function sszInstallBanner() {
        return {
            visible: false,
            deferredPrompt: null,

            init() {
                window.addEventListener('beforeinstallprompt', (event) => {
                    event.preventDefault();
                    this.deferredPrompt = event;

                    const snoozedUntil = Number(localStorage.getItem('ssz.install.snooze') || 0);

                    if (Date.now() > snoozedUntil) {
                        this.visible = true;
                    }
                });
            },

            async install() {
                if (! this.deferredPrompt) {
                    return;
                }

                this.deferredPrompt.prompt();
                await this.deferredPrompt.userChoice;

                this.deferredPrompt = null;
                this.visible = false;
            },

            dismiss() {
                // Tunda 7 hari.
                localStorage.setItem('ssz.install.snooze', String(Date.now() + 7 * 24 * 60 * 60 * 1000));
                this.visible = false;
            },
        };
    }
</script>
@endpush
