@extends('errors.layout')
@section('code','419')@section('code_label','Page Expired')@section('icon','bi-clock-history')@section('badge','Sesi Kedaluwarsa')
@section('title','Halaman Kedaluwarsa')
@section('message','Sesi Anda telah kedaluwarsa karena tidak ada aktivitas atau token keamanan tidak valid. Muat ulang dan coba lagi.')
@section('hint','Tekan Muat Ulang lalu ulangi aksi Anda. Jangan membuka banyak tab bersamaan.')
@section('action')
<a href="{{ url()->current() }}" onclick="location.reload();return false" class="btn btn-primary"><i class="bi bi-arrow-clockwise"></i> Muat Ulang</a>
<a href="{{ route('login') }}" class="btn btn-ghost"><i class="bi bi-box-arrow-in-right"></i> Login</a>
@endsection
