<?php

/*
| SATU-SATUNYA tempat mengatur foto website (dipakai Blade & JavaScript).
| Untuk mengganti foto: ubah 'url' di bawah. Bisa link (Unsplash dsb.) atau
| file lokal, mis. '/images/hero-1.jpg' (simpan di folder public/images).
*/
$u = fn (string $id, int $w = 1600) => "https://images.unsplash.com/photo-{$id}?auto=format&fit=crop&w={$w}&q=80";

return [
    // Foto bergantian di beranda
    'hero' => [
        ['url' => $u('1566073771259-6a8506099945'), 'alt' => 'Lobi dan kolam renang hotel', 'judul' => 'Kolam renang & taman'],
        ['url' => $u('1542314831-068cd1dbfeeb'), 'alt' => 'Bangunan hotel', 'judul' => 'Hangat sejak pintu masuk'],
        ['url' => $u('1582719478250-c89cae4dc85b'), 'alt' => 'Suite hotel', 'judul' => 'Suite yang lapang'],
        ['url' => $u('1520250497591-112f2f40a3f4'), 'alt' => 'Resort dan kolam', 'judul' => 'Santai di tepi kolam'],
        ['url' => $u('1551882547-ff40c63fe5fa'), 'alt' => 'Hotel saat senja', 'judul' => 'Senja yang tenang'],
    ],

    // Foto per tipe kamar (kunci = nama tipe huruf kecil), 'default' dipakai bila tipe baru
    'kamar' => [
        'standard' => $u('1631049307264-da0ec9d70304', 900),
        'deluxe' => $u('1590490360182-c33d57733427', 900),
        'family' => $u('1618773928121-c32242e63f39', 900),
        'suite' => $u('1582719478250-c89cae4dc85b', 900),
        'default' => $u('1578683010236-d716f9a3f461', 900),
    ],

    'fasilitas' => $u('1571896349842-33c89424de2d', 1400),
    'auth' => $u('1566073771259-6a8506099945', 1400),
];
