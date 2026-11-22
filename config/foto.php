<?php

/*
| SATU-SATUNYA tempat mengatur foto website (dipakai Blade & JavaScript).
|
| 'url'      = foto utama (link internet, atau file lokal mis. '/images/hero-1.jpg').
| 'cadangan' = ilustrasi lokal di public/images/ilustrasi. Otomatis tampil bila 'url'
|              gagal dimuat (internet mati / link rusak), jadi tidak pernah kosong.
|
| Untuk memakai foto hotel sendiri: simpan di public/images lalu ganti 'url'.
*/
$f = fn (string $id, int $lebar, string $cadangan) => [
    'url' => "https://images.unsplash.com/photo-{$id}?auto=format&fit=crop&w={$lebar}&q=80",
    'cadangan' => "/images/ilustrasi/{$cadangan}.svg",
];

return [
    // Foto bergantian di beranda
    'hero' => [
        $f('1566073771259-6a8506099945', 1600, 'hero-1') + ['alt' => 'Lobi dan kolam renang hotel', 'judul' => 'Kolam renang & taman'],
        $f('1542314831-068cd1dbfeeb', 1600, 'hero-2') + ['alt' => 'Bangunan hotel', 'judul' => 'Hangat sejak pintu masuk'],
        $f('1582719478250-c89cae4dc85b', 1600, 'hero-3') + ['alt' => 'Suite hotel', 'judul' => 'Suite yang lapang'],
        $f('1520250497591-112f2f40a3f4', 1600, 'hero-4') + ['alt' => 'Resort dan kolam', 'judul' => 'Santai di tepi kolam'],
        $f('1551882547-ff40c63fe5fa', 1600, 'hero-5') + ['alt' => 'Hotel saat senja', 'judul' => 'Senja yang tenang'],
    ],

    // Foto per tipe kamar (kunci = nama tipe huruf kecil); 'default' dipakai untuk tipe baru
    'kamar' => [
        'standard' => $f('1631049307264-da0ec9d70304', 900, 'kamar-standard'),
        'deluxe' => $f('1590490360182-c33d57733427', 900, 'kamar-deluxe'),
        'family' => $f('1618773928121-c32242e63f39', 900, 'kamar-family'),
        'suite' => $f('1582719478250-c89cae4dc85b', 900, 'kamar-suite'),
        'default' => $f('1578683010236-d716f9a3f461', 900, 'kamar-default'),
    ],

    // Galeri fasilitas (slide di beranda). Ubah/tambah baris sesuai fasilitas hotel.
    'fasilitas' => [
        $f('1521737604893-d14cc237f11d', 1400, 'fas-wifi') + ['nama' => 'Wi-Fi cepat', 'deskripsi' => 'Internet cepat di seluruh area hotel untuk bekerja maupun bersantai.', 'ikon' => 'wifi'],
        $f('1506521781263-d8422e82f27a', 1400, 'fas-parkir') + ['nama' => 'Parkir luas', 'deskripsi' => 'Area parkir luas dan aman untuk mobil maupun motor tamu.', 'ikon' => 'parkir'],
        $f('1540555700478-4be289fbecef', 1400, 'fas-spa') + ['nama' => 'Spa', 'deskripsi' => 'Pijat dan perawatan relaksasi untuk melepas lelah setelah perjalanan.', 'ikon' => 'spa'],
        $f('1571896349842-33c89424de2d', 1400, 'fasilitas') + ['nama' => 'Kolam renang', 'deskripsi' => 'Kolam outdoor dengan area santai di tepi kolam dan taman.', 'ikon' => 'kolam'],
        $f('1517248135467-4c7edcad34c4', 1400, 'fas-resto') + ['nama' => 'Resto', 'deskripsi' => 'Sarapan hingga makan malam dengan pilihan menu lokal dan internasional.', 'ikon' => 'resto'],
        $f('1497366216548-37526070297c', 1400, 'fas-rapat') + ['nama' => 'Ruang pertemuan', 'deskripsi' => 'Ruang rapat lengkap dengan proyektor untuk acara dan pertemuan.', 'ikon' => 'rapat'],
    ],
    'auth' => $f('1566073771259-6a8506099945', 1400, 'auth'),
];
