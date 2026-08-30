@extends('errors.layout')

@section('title', 'Halaman Tidak Ditemukan')
@section('message', 'Halaman yang Anda cari tidak tersedia atau sudah dipindahkan.')

@section('action')
<a href="{{ url('/') }}" class="btn">Kembali ke Beranda</a>
@endsection
