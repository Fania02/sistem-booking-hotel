<?php

it('menampilkan landing page', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Menginap yang')
        ->assertSee('Pilih ruang yang cocok');
});

it('menampilkan halaman admin reservasi', function () {
    $this->get('/admin/reservasi')
        ->assertOk()
        ->assertSee('Kelola booking, status, dan check-in tamu.');
});

it('menampilkan halaman admin tamu, tipe kamar, dan fasilitas', function () {
    $this->get('/admin/tamu')
        ->assertOk()
        ->assertSee('Tamu, Tipe Kamar &amp; Fasilitas', false)
        ->assertSee('Tambah Fasilitas Hotel');
});
