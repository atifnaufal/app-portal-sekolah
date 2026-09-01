@extends('errors.layout')
@section('code','403')@section('code_label','Forbidden')@section('icon','bi-shield-x')@section('badge','Akses Ditolak')
@section('title','Akses Ditolak')
@section('message','Anda tidak memiliki izin untuk mengakses halaman ini. Hubungi admin jika Anda merasa ini kesalahan.')
@section('hint','Peran akun Anda mungkin tidak memiliki akses ke fitur ini.')
@section('action')
<a href="{{ url('/') }}" class="btn btn-primary"><i class="bi bi-house"></i> Beranda</a>
<a href="{{ url()->previous() }}" class="btn btn-ghost"><i class="bi bi-arrow-left"></i> Kembali</a>
@endsection
