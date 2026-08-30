@extends('errors.layout')

@section('title', 'Sesi Berakhir')
@section('message', 'Sesi Anda telah berakhir. Silakan masuk kembali untuk melanjutkan.')

@section('action')
<a href="{{ url('/') }}" class="btn">Masuk Kembali</a>
@endsection
