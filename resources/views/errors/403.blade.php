@extends('errors.layout')

@section('title', 'Akses Ditolak')
@section('message', 'Anda tidak memiliki izin untuk mengakses halaman ini.')

@section('action')
<a href="{{ url('/') }}" class="btn">Kembali ke Beranda</a>
@endsection
