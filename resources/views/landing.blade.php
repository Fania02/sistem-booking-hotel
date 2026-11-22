@extends('layouts.publik')

@section('title', 'Royal Hotel — Menginap yang terasa seperti pulang')
@section('page', 'landing')

@section('content')
<div class="mx-auto max-w-6xl px-5">

    {{-- ===================== HERO + SLIDER ===================== --}}
    <section id="beranda" class="relative">
        <div class="grid overflow-hidden rounded-3xl bg-tinta text-white md:grid-cols-[1.05fr_1fr]">

            {{-- Teks hero kiri --}}
            <div class="order-2 px-7 pb-10 pt-8 md:order-1 md:px-10 md:pb-24 md:pt-14 flex flex-col justify-center">
                <span class="inline-block rounded-full bg-white/10 px-3 py-1 text-[10px] font-medium tracking-wide text-pasir">Hangat · Tenang · Nyaman</span>
                <h1 class="judul mt-5 text-4xl leading-[1.08] md:text-5xl">
                    Menginap yang<br>terasa seperti<br><em class="text-emas">pulang</em>
                </h1>
                <p class="mt-4 max-w-sm text-xs leading-relaxed text-white/65">
                    Pilih tipe kamar, cek ketersediaan, dan booking dalam satu alur yang sederhana.
                </p>
                <div class="mt-6 flex flex-wrap gap-2">
                    <a href="#tipe-kamar" class="btn-emas hover-btn">Lihat Kamar</a>
                    <a href="#form-cari" class="btn-garis-terang">Cek Reservasi</a>
                </div>

                {{-- Dot navigasi slider --}}
                <div id="slider-dots" class="mt-8 flex gap-2" role="group" aria-label="Navigasi slider"></div>
            </div>

            {{-- Slider gambar kanan --}}
            <div class="order-1 relative bg-kopi md:order-2 overflow-hidden" style="min-height: 320px;">
                <div id="hero-slider" class="absolute inset-0">
                    {{-- Slide 1 --}}
                    <div class="hero-slide aktif" style="background-image:url('https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800&q=80');"></div>
                    {{-- Slide 2 --}}
                    <div class="hero-slide" style="background-image:url('https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=800&q=80');"></div>
                    {{-- Slide 3 --}}
                    <div class="hero-slide" style="background-image:url('https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800&q=80');"></div>
                    {{-- Slide 4 --}}
                    <div class="hero-slide" style="background-image:url('https://images.unsplash.com/photo-1455587734955-081b22074882?w=800&q=80');"></div>
                </div>
                {{-- Overlay gradient pada slider --}}
                <div class="absolute inset-0 bg-gradient-to-t from-tinta/30 to-transparent pointer-events-none"></div>
            </div>
        </div>

        {{-- Form cari kamar --}}
        <form id="form-cari" class="relative z-10 mt-4 grid scroll-mt-24 grid-cols-2 gap-3 rounded-2xl bg-white p-4 shadow-[0_12px_32px_-12px_rgba(43,31,26,0.35)] md:absolute md:inset-x-10 md:bottom-0 md:mt-0 md:translate-y-1/2 md:grid-cols-[1fr_1fr_0.7fr_1fr_auto] md:items-end md:p-5" novalidate>
            <div>
                <label class="label" for="cari-checkin">Check-in</label>
                <input class="input-krem" type="date" id="cari-checkin" name="tgl_checkin">
            </div>
            <div>
                <label class="label" for="cari-checkout">Check-out</label>
                <input class="input-krem" type="date" id="cari-checkout" name="tgl_checkout">
            </div>
            <div>
                <label class="label" for="cari-tamu">Tamu</label>
                <select class="input-krem" id="cari-tamu" name="jumlah_tamu">
                    @foreach ([1, 2, 3, 4] as $jumlah)
                        <option value="{{ $jumlah }}" @selected($jumlah === 2)>{{ $jumlah }} tamu</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label" for="cari-tipe">Tipe Kamar</label>
                <select class="input-krem" id="cari-tipe" name="tipe_kamar_id">
                    <option value="">Semua tipe</option>
                </select>
            </div>
            <button type="submit" class="btn-gelap col-span-2 h-[38px] hover-btn md:col-span-1 md:px-6">Cari Kamar</button>
            <p id="cari-error" class="pesan-error col-span-2 md:col-span-5" hidden></p>
        </form>
    </section>

    {{-- ===================== KEUNGGULAN ===================== --}}
    <section class="mt-4 stagger grid grid-cols-2 gap-3 md:mt-24 md:grid-cols-4 anim-fade">
        @foreach ([
            ['🏷️', 'Tipe Kamar Lengkap', 'Harga dan kapasitas terlihat jelas sebelum memesan.'],
            ['⚡', 'Konfirmasi Cepat', 'Kode booking langsung terbit setelah data dikirim.'],
            ['💳', 'Bayar Mudah', 'Pembayaran dicatat dan dipantau oleh resepsionis.'],
            ['✨', 'Fasilitas Dasar', 'Wi-Fi, AC, dan sarapan tersedia di tiap kamar.'],
        ] as [$ikon, $judul, $isi])
            <div class="kartu kartu-shine p-4 hover-kartu cursor-default">
                <span class="text-xl">{{ $ikon }}</span>
                <h3 class="mt-2 text-[11px] font-semibold">{{ $judul }}</h3>
                <p class="mt-1 text-[10px] leading-relaxed text-redam">{{ $isi }}</p>
            </div>
        @endforeach
    </section>

    {{-- ===================== HASIL PENCARIAN ===================== --}}
    <section id="hasil-cari" class="mt-12" hidden>
        <div class="flex items-end justify-between">
            <div>
                <p class="eyebrow">Hasil pencarian</p>
                <h2 class="judul mt-1 text-2xl" id="hasil-judul">Kamar tersedia</h2>
            </div>
            <button type="button" id="hasil-tutup" class="text-xs font-medium text-redam hover:text-tinta">Tutup ✕</button>
        </div>
        <div id="hasil-daftar" class="mt-5 grid gap-4 md:grid-cols-3"></div>
    </section>

    {{-- ===================== TIPE KAMAR ===================== --}}
    <section id="tipe-kamar" class="mt-16 scroll-mt-20 anim-fade">
        <div class="flex items-end justify-between">
            <div>
                <p class="eyebrow">Pilihan kamar</p>
                <h2 class="judul mt-1 text-2xl md:text-3xl">Pilih ruang yang cocok</h2>
            </div>
            <button type="button" id="tipe-semua" class="text-xs font-semibold hover:text-emas-tua" hidden>Lihat semua →</button>
        </div>
        <div id="tipe-daftar" class="mt-6 grid gap-4 md:grid-cols-3">
            @foreach (range(1, 3) as $i)
                <div class="kartu h-72 animate-pulse"></div>
            @endforeach
        </div>
    </section>

    {{-- ===================== FASILITAS ===================== --}}
    <section id="fasilitas" class="mt-20 grid scroll-mt-20 items-center gap-8 md:grid-cols-[1.1fr_1fr] anim-fade">
        {{-- Foto fasilitas nyata --}}
        <div class="kartu-gambar rounded-3xl overflow-hidden aspect-[4/3] relative">
            <img
                src="https://images.unsplash.com/photo-1540541338287-41700207dee6?w=900&q=85"
                alt="Fasilitas Royal Hotel — Kolam renang dan area relaksasi"
                class="w-full h-full object-cover"
                loading="lazy"
            >
            <div class="absolute inset-0 bg-gradient-to-t from-tinta/40 to-transparent"></div>
            <div class="absolute bottom-5 left-5 text-white">
                <p class="text-[10px] font-medium uppercase tracking-widest text-pasir">Royal Hotel</p>
                <p class="judul text-lg">Kolam Renang & Spa</p>
            </div>
        </div>

        <div>
            <p class="eyebrow">Fasilitas</p>
            <h2 class="judul mt-1 text-2xl md:text-3xl">Lebih dari sekadar kamar</h2>
            <p class="mt-3 text-xs leading-relaxed text-redam">
                Nikmati berbagai fasilitas premium yang dirancang untuk kenyamanan maksimal Anda selama menginap.
            </p>
            <div class="mt-4 grid grid-cols-2 gap-3">
                @foreach ([
                    ['🏊', 'Kolam Renang', 'Outdoor & indoor tersedia'],
                    ['💆', 'Spa & Wellness', 'Layanan relaksasi penuh'],
                    ['🍽️', 'Restoran', 'Masakan nusantara & internasional'],
                    ['📶', 'Wi-Fi Cepat', 'Hingga 1 Gbps di seluruh area'],
                    ['🅿️', 'Parkir Luas', 'Gratis untuk tamu hotel'],
                    ['🏢', 'Ruang Meeting', 'Kapasitas hingga 200 orang'],
                ] as [$ikon, $nama, $sub])
                    <div class="flex items-start gap-2 rounded-xl p-2 hover:bg-white transition">
                        <span class="text-base mt-0.5">{{ $ikon }}</span>
                        <div>
                            <p class="text-[11px] font-semibold">{{ $nama }}</p>
                            <p class="text-[10px] text-redam">{{ $sub }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
            <a href="#kontak" class="btn-gelap mt-5 hover-btn">Hubungi Kami</a>
        </div>
    </section>

    {{-- ===================== GALERI FOTO ===================== --}}
    <section class="mt-20 anim-fade">
        <div class="text-center mb-8">
            <p class="eyebrow">Galeri</p>
            <h2 class="judul mt-1 text-2xl md:text-3xl">Lihat keindahan hotel kami</h2>
        </div>
        <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
            @foreach ([
                ['https://images.unsplash.com/photo-1512918728675-ed5a9ecdebfd?w=600&q=80', 'Kamar Deluxe', 'md:row-span-2'],
                ['https://images.unsplash.com/photo-1584132967334-10e028bd69f7?w=600&q=80', 'Lobby Utama', ''],
                ['https://images.unsplash.com/photo-1562778612-e1e0cda9915c?w=600&q=80', 'Restoran', ''],
                ['https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=600&q=80', 'Kolam Renang', 'md:col-span-2'],
            ] as [$src, $alt, $kelas])
                <div class="kartu-gambar rounded-2xl overflow-hidden {{ $kelas }}">
                    <img src="{{ $src }}" alt="{{ $alt }}" class="w-full h-40 object-cover md:h-full" loading="lazy">
                </div>
            @endforeach
        </div>
    </section>

    {{-- ===================== CARA PESAN ===================== --}}
    <section id="cara-pesan" class="mt-20 scroll-mt-20 text-center anim-fade">
        <p class="eyebrow">Cara pesan</p>
        <h2 class="judul mt-1 text-2xl md:text-3xl">Tiga langkah menuju liburan</h2>
        <div class="mt-6 stagger grid gap-4 text-left md:grid-cols-3">
            @foreach ([
                ['01', 'Pilih kamar', 'Tentukan tanggal dan tipe kamar yang kamu inginkan.', '🔍'],
                ['02', 'Isi data diri', 'Masukkan nama, NIK 16 digit, dan nomor HP tamu.', '📝'],
                ['03', 'Terima kode booking', 'Simpan kode booking dan tunjukkan saat check-in.', '🎉'],
            ] as [$nomor, $judul, $isi, $ikon])
                <div class="kartu kartu-shine p-5 hover-kartu cursor-default">
                    <div class="flex items-center gap-3">
                        <span class="judul text-xl text-emas">{{ $nomor }}</span>
                        <span class="text-xl">{{ $ikon }}</span>
                    </div>
                    <h3 class="mt-2 text-xs font-semibold">{{ $judul }}</h3>
                    <p class="mt-1 text-[11px] leading-relaxed text-redam">{{ $isi }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ===================== TESTIMONI ===================== --}}
    <section class="mt-20 anim-fade">
        <p class="eyebrow text-center">Testimoni</p>
        <h2 class="judul mt-1 text-center text-2xl md:text-3xl">Apa kata tamu kami</h2>
        <div class="mt-6 stagger grid gap-4 md:grid-cols-3">
            @foreach ([
                ['Pelayanan luar biasa! Staf sangat ramah dan kamarnya bersih banget. Pasti balik lagi.', 'Budi Santoso', 'Jakarta', '⭐⭐⭐⭐⭐'],
                ['Fasilitas kolam renangnya keren. Lokasi juga strategis. Worth every penny!', 'Sari Dewi', 'Bandung', '⭐⭐⭐⭐⭐'],
                ['Check-in cepat, kamar nyaman, sarapannya enak. Recommended buat keluarga!', 'Andi Pratama', 'Surabaya', '⭐⭐⭐⭐⭐'],
            ] as [$ulasan, $nama, $kota, $bintang])
                <div class="kartu kartu-shine p-5 hover-kartu cursor-default">
                    <p class="text-sm">{{ $bintang }}</p>
                    <p class="mt-3 text-[11px] leading-relaxed text-redam italic">"{{ $ulasan }}"</p>
                    <div class="mt-4 flex items-center gap-2">
                        <div class="size-7 rounded-full bg-emas/20 flex items-center justify-center text-xs font-bold text-emas-tua">{{ substr($nama, 0, 1) }}</div>
                        <div>
                            <p class="text-[11px] font-semibold">{{ $nama }}</p>
                            <p class="text-[10px] text-redam">{{ $kota }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ===================== CTA ===================== --}}
    <section id="kontak" class="mt-16 relative overflow-hidden flex flex-col items-start justify-between gap-4 rounded-3xl bg-tinta px-7 py-10 text-white md:flex-row md:items-center md:px-10">
        {{-- Background dekoratif --}}
        <div class="absolute -right-16 -top-16 size-64 rounded-full bg-emas/10 pointer-events-none"></div>
        <div class="absolute -left-8 -bottom-8 size-40 rounded-full bg-white/5 pointer-events-none"></div>

        <div class="relative">
            <h2 class="judul text-2xl md:text-3xl">Siap menginap bersama kami?</h2>
            <p class="mt-1 text-xs text-white/60">Booking dalam hitungan menit, tanpa ribet.</p>
            <div class="mt-3 flex flex-wrap gap-4 text-xs text-white/70">
                <span>📞 (021) 123-4567</span>
                <span>✉️ info@royalhotel.id</span>
                <span>📍 Jl. Merdeka No. 1</span>
            </div>
        </div>
        <a href="#form-cari" class="relative btn-emas hover-btn shrink-0">Pesan Sekarang</a>
    </section>
</div>
@endsection

@push('modal')
    @include('partials.modal-booking')
@endpush
