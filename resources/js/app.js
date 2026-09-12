import './bootstrap';

// Utilitas kecil yang dipakai lintas halaman (tanpa framework tambahan).
window.SSZ = {
    /** Getar singkat bila perangkat mendukung. */
    vibrate(pattern) {
        if (navigator.vibrate) {
            navigator.vibrate(pattern);
        }
    },

    /** Bunyi umpan balik pendek memakai Web Audio (tanpa file audio). */
    beep(frequency = 880, duration = 140, type = 'sine') {
        try {
            const Ctx = window.AudioContext || window.webkitAudioContext;
            if (!Ctx) return;
            const ctx = (window.__sszAudioCtx ||= new Ctx());
            if (ctx.state === 'suspended') ctx.resume();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = type;
            osc.frequency.value = frequency;
            gain.gain.setValueAtTime(0.0001, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.25, ctx.currentTime + 0.01);
            gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + duration / 1000);
            osc.connect(gain).connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + duration / 1000);
        } catch (e) {
            /* abaikan: umpan balik suara bersifat opsional */
        }
    },
};

// Registrasi service worker PWA.
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    });
}
