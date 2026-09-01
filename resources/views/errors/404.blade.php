@extends('errors.layout')
@section('code','404')@section('code_label','Not Found')@section('icon','bi-search')@section('badge','Tidak Ditemukan')
@section('title','Halaman Tidak Ditemukan')
@section('message','Halaman yang Anda cari tidak tersedia, sudah dipindahkan, atau URL salah ketik.')
@section('hint','Periksa kembali URL atau gunakan menu navigasi untuk mencari halaman yang diinginkan.')
@section('action')
<a href="{{ url('/') }}" class="btn btn-primary"><i class="bi bi-house"></i> Beranda</a>
<a href="{{ url()->previous() }}" class="btn btn-ghost"><i class="bi bi-arrow-left"></i> Kembali</a>
@endsection
