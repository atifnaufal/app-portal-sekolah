@extends('errors.layout')
@section('code','429')@section('code_label','Too Many Requests')@section('icon','bi-hourglass-split')@section('badge','Batas Permintaan')
@section('title','Terlalu Banyak Permintaan')
@section('message','Anda melakukan terlalu banyak permintaan dalam waktu singkat. Sistem menahan sementara untuk keamanan.')
@section('hint','Tunggu 30-60 detik lalu coba lagi. Hindari menekan tombol berulang kali.')
@section('action')
<a href="{{ url()->previous() }}" class="btn btn-ghost"><i class="bi bi-arrow-left"></i> Kembali</a>
<a href="{{ url('/') }}" class="btn btn-primary"><i class="bi bi-house"></i> Beranda</a>
@endsection
