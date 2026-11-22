@extends('layouts.publik')

@section('title', 'Riwayat Saya · Royal Hotel')
@section('page', 'tamu-dashboard')

@section('content')
<div class="mx-auto max-w-6xl px-5 py-10">
    <p class="eyebrow">Halo, {{ auth()->user()->name }}</p>
    <h1 class="judul mt-1 text-3xl">Riwayat booking</h1>
    <div class="kartu mt-6 p-8 text-center text-xs text-redam" data-reveal>Riwayat, upload bukti bayar, dan pembatalan menyusul di langkah berikutnya.</div>
</div>
@endsection
