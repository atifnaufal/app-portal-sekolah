@extends('errors.layout')

@section('title', 'Terjadi Kesalahan')
@section('message', 'Terjadi kesalahan pada sistem. Tim teknis kami telah diberitahu dan sedang menangani masalah ini. Silakan coba lagi beberapa saat.')

@section('action')
<a href="{{ url('/') }}" class="btn">Coba Lagi</a>
@endsection
