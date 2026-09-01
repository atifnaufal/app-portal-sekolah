@extends('errors.layout')
@section('code','500')@section('code_label','Server Error')@section('icon','bi-cpu')@section('badge','Gangguan Server')
@section('title','Terjadi Kesalahan Server')
@section('message','Terjadi kesalahan pada server. Tim teknis telah diberitahu. Silakan coba beberapa saat lagi.')
@section('hint','Jika terus terjadi, salin detail error dan kirim ke Admin IT beserta waktu kejadian.')
@section('action')
<a href="{{ url()->current() }}" onclick="location.reload();return false" class="btn btn-primary"><i class="bi bi-arrow-clockwise"></i> Coba Lagi</a>
<a href="{{ url('/') }}" class="btn btn-ghost"><i class="bi bi-house"></i> Beranda</a>
@endsection
