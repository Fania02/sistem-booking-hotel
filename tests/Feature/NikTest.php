<?php

use App\Rules\Nik;
use Illuminate\Support\Facades\Validator;

function nikValid(mixed $nik): bool
{
    return Validator::make(['nik' => $nik], ['nik' => [new Nik]])->passes();
}

it('menerima NIK 16 digit dengan kode provinsi yang valid', function (string $nik) {
    expect(nikValid($nik))->toBeTrue();
})->with(['3372010101010001', '3374011234560001', '1101010101010001', '9101010101010001']);

it('menolak NIK dengan panjang, karakter, atau kode provinsi yang salah', function (mixed $nik) {
    expect(nikValid($nik))->toBeFalse();
})->with(['123', '33720101010100012', '33720101010100a1', '3372 0101 0101 0001', '0072010101010001', '2072010101010001', '9972010101010001', 3372010101010001]);

it('menerima semua NIK pada data contoh seeder', function () {
    $daftar = ['3374011234560001', '3374011234560002', '3372011234560003', '3372011234560004', '3471011234560005', '3578011234560006'];

    foreach ($daftar as $nik) {
        expect(nikValid($nik))->toBeTrue("NIK seeder {$nik} harus valid");
    }
});
