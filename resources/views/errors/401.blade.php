@extends('errors.layout')
@section('code','401')@section('code_label','Unauthorized')@section('icon','bi-shield-lock-fill')@section('badge','Sesi Berakhir')
@section('title','Sesi Berakhir / Belum Login')
@section('message','Sesi Anda telah berakhir atau Anda belum masuk. Silakan login kembali untuk melanjutkan.')
@section('hint','Jika baru saja login dan masih muncul, coba hapus cache browser / tutup dan buka kembali aplikasi.')
@section('action')
<a href="{{ route('login') }}" class="btn btn-primary"><i class="bi bi-box-arrow-in-right"></i> Masuk Kembali</a>
<a href="{{ url('/') }}" class="btn btn-ghost"><i class="bi bi-house"></i> Beranda</a>
@endsection
