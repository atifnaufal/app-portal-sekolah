@extends('errors.layout')

@section('title', 'Sementara Tidak Tersedia')
@section('message', 'Sistem sedang dalam pemeliharaan. Silakan coba kembali beberapa saat lagi.')

@section('action')
<a href="{{ url('/') }}" class="btn">Muat Ulang</a>
@endsection
