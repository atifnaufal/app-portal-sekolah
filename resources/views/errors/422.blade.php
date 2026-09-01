@extends('errors.layout')
@section('code','422')@section('code_label','Unprocessable')@section('icon','bi-exclamation-diamond-fill')@section('badge','Validasi Gagal')
@section('title','Data Tidak Valid')
@section('message','Data yang Anda kirim tidak valid. Periksa kembali isian form dan pastikan sesuai aturan.')
@section('hint','Perhatikan pesan validasi di dekat field yang ditandai merah.')
@section('action')
<a href="{{ url()->previous() }}" class="btn btn-primary"><i class="bi bi-arrow-left"></i> Kembali & Perbaiki</a>
@endsection
