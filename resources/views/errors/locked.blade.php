@extends('errors.layout')
@section('code','Terkunci')@section('code_label','Fitur Terkunci')@section('icon','bi-lock-fill')@section('badge','Akses Ditutup')
@section('title','Fitur Ini Sedang Dikunci')
@section('message', $msg ?? 'Fitur ini sedang dinonaktifkan oleh admin. Silakan hubungi admin sekolah atau Admin Pusat untuk membukanya.')
@section('hint','Akun baru juga harus disetujui admin sebelum bisa digunakan. Jika pendaftaran ditutup, tombol daftar tidak akan muncul.')
@section('action')
<a href="{{ url()->previous() }}" class="btn btn-ghost"><i class="bi bi-arrow-left"></i> Kembali</a>
<a href="{{ url('/') }}" class="btn btn-primary"><i class="bi bi-house"></i> Beranda</a>
@endsection
