@extends('errors.layout')

@section('title', 'Terlalu Banyak Permintaan')
@section('message', 'Anda melakukan terlalu banyak permintaan dalam waktu singkat. Silakan tunggu beberapa saat sebelum mencoba lagi.')

@section('action')
<a href="{{ url()->previous() }}" class="btn">Kembali</a>
@endsection
