import { api } from '../lib/api';
import { escapeHtml, formatRupiah, formatTanggal } from '../lib/format';
import { $, $$ } from '../lib/ui';

const WARNA_STATUS = {
    menunggu: 'bg-amber-100 text-amber-800',
    dikonfirmasi: 'bg-sky-100 text-sky-800',
    checkin: 'bg-emerald-100 text-emerald-800',
    selesai: 'bg-stone-200 text-stone-700',
    batal: 'bg-red-100 text-red-700',
};

/** Hitung naik dari 0 supaya angka terasa hidup. */
function hitungNaik(el, akhir, format = (n) => String(n)) {
    const mulai = performance.now();
    const durasi = 900;

    const langkah = (sekarang) => {
        const t = Math.min(1, (sekarang - mulai) / durasi);
        el.textContent = format(Math.round(akhir * (1 - (1 - t) ** 3)));

        if (t < 1) requestAnimationFrame(langkah);
    };

    requestAnimationFrame(langkah);
}

function grafik(data) {
    const maks = Math.max(1, ...data.map((d) => d.jumlah));

    $('#grafik').innerHTML = data
        .map(
            (d) => `
            <div class="flex h-full flex-1 flex-col items-center justify-end gap-1.5">
                <span class="text-[10px] font-semibold">${d.jumlah}</span>
                <div class="w-full rounded-t-lg bg-emas/80 transition-all duration-[900ms] ease-out hover:bg-emas-tua" style="height:0" data-tinggi="${Math.max(4, (d.jumlah / maks) * 100)}"></div>
                <span class="text-[10px] capitalize text-redam">${escapeHtml(d.hari)}</span>
            </div>`,
        )
        .join('');

    requestAnimationFrame(() =>
        requestAnimationFrame(() =>
            $$('[data-tinggi]').forEach((el) => {
                el.style.height = `${el.dataset.tinggi}%`;
            }),
        ),
    );
}

export async function initAdminDashboard() {
    $('#tanggal-hari-ini').textContent = new Intl.DateTimeFormat('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(new Date());

    try {
        let data;

        // Coba endpoint backend utama terlebih dahulu.
        try {
            ({ data } = await api.get('/dashboard'));
        } catch {
            // Gunakan endpoint alternatif dari branch frontend.
            ({ data } = await api.get('/admin/ringkasan'));
        }

        // Samakan format respons dari kedua endpoint.
        const totalKamar = data.kamar?.total ?? data.total_kamar ?? 0;
        const kamarTerisi = data.kamar?.terisi ?? data.kamar_terisi ?? 0;
        const okupansi = data.okupansi
            ?? (totalKamar > 0 ? Math.round((kamarTerisi / totalKamar) * 100) : 0);

        const angka = {
            checkin_hari_ini: data.reservasi?.checkin_hari_ini ?? data.checkin_hari_ini ?? 0,
            checkout_hari_ini: data.reservasi?.checkout_hari_ini ?? data.checkout_hari_ini ?? 0,
            pembayaran_menunggu: data.perlu_tindakan?.pembayaran_menunggu_verifikasi
                ?? data.pembayaran_menunggu ?? 0,
            pembatalan_diajukan: data.perlu_tindakan?.pembatalan_diajukan
                ?? data.pembatalan_diajukan ?? 0,
        };

        const isi = (kunci, nilai, format) =>
            hitungNaik($(`[data-stat="${kunci}"]`), nilai, format);

        isi('checkin_hari_ini', angka.checkin_hari_ini);
        isi('checkout_hari_ini', angka.checkout_hari_ini);
        isi('okupansi', okupansi, (n) => `${n}%`);
        isi('pembayaran_menunggu', angka.pembayaran_menunggu);
        isi('pembatalan_diajukan', angka.pembatalan_diajukan);

        // Pendapatan hanya ditampilkan jika dikirim oleh backend.
        const pendapatan = data.pendapatan?.bulan_ini ?? data.pendapatan_bulan_ini;

        if (pendapatan !== undefined && pendapatan !== null) {
            isi('pendapatan_bulan_ini', pendapatan, formatRupiah);
        } else {
            $('[data-stat="pendapatan_bulan_ini"]').textContent = '–';
        }

        ['pembayaran_menunggu', 'pembatalan_diajukan'].forEach((kunci) => {
            if (angka[kunci] > 0) {
                const lencana = $(`[data-stat="${kunci}"]`);
                lencana.classList.remove('bg-pasir');
                lencana.classList.add('bg-emas', 'text-white');
            }
        });

        grafik(data.grafik_kedatangan ?? []);

        requestAnimationFrame(() => {
            $('#cincin').style.strokeDashoffset = String(276.46 * (1 - okupansi / 100));
        });

        hitungNaik($('#cincin-angka'), okupansi, (n) => `${n}%`);
        $('#cincin-info').textContent = `${kamarTerisi} dari ${totalKamar} kamar terisi`;

        const terbaru = data.reservasi_terbaru ?? [];

        $('#terbaru').innerHTML = terbaru.length
            ? terbaru
                  .map((r) => {
                      const namaTamu = typeof r.tamu === 'object'
                          ? r.tamu?.nama ?? '-'
                          : r.tamu ?? '-';

                      const status = typeof r.status === 'object'
                          ? r.status
                          : { value: r.status, label: r.status };

                      return `
                <div class="flex flex-wrap items-center justify-between gap-2 py-3 text-xs transition hover:bg-krem/60">
                    <div>
                        <p class="font-semibold">${escapeHtml(r.kode_booking)} · ${escapeHtml(namaTamu)}</p>
                        <p class="text-[11px] text-redam">${formatTanggal(r.tgl_checkin)} – ${formatTanggal(r.tgl_checkout)}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="font-medium">${formatRupiah(r.total_harga)}</span>
                        <span class="badge ${WARNA_STATUS[status.value] ?? ''}">${escapeHtml(status.label ?? '-')}</span>
                    </div>
                </div>`;
                  })
                  .join('')
            : '<p class="py-6 text-center text-xs text-redam">Belum ada reservasi.</p>';
    } catch (error) {
        $('#terbaru').innerHTML = `<p class="py-4 text-xs text-red-600">Gagal memuat dashboard: ${escapeHtml(error.message)}</p>`;
    }
}