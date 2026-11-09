const rupiah = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 });

export const formatRupiah = (angka) => rupiah.format(angka ?? 0).replace(/\s/g, ' ');

export function formatTanggal(isoTanggal, opsi = { day: '2-digit', month: 'short' }) {
    if (!isoTanggal) {
        return '-';
    }

    return new Intl.DateTimeFormat('id-ID', opsi).format(new Date(`${isoTanggal.slice(0, 10)}T00:00:00`));
}

export function formatWaktu(isoWaktu) {
    if (!isoWaktu) {
        return '-';
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(isoWaktu));
}

export const hariIni = () => new Date(Date.now() - new Date().getTimezoneOffset() * 60000).toISOString().slice(0, 10);

export function tambahHari(isoTanggal, jumlah) {
    const tanggal = new Date(`${isoTanggal}T00:00:00`);
    tanggal.setDate(tanggal.getDate() + jumlah);

    return new Date(tanggal.getTime() - tanggal.getTimezoneOffset() * 60000).toISOString().slice(0, 10);
}

export const escapeHtml = (teks) =>
    String(teks ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);