// Bundel khusus halaman scan petugas: memuat pustaka html5-qrcode dari npm (tanpa CDN).
import { Html5Qrcode } from 'html5-qrcode';

window.Html5Qrcode = Html5Qrcode;
window.dispatchEvent(new Event('html5qrcode-ready'));
