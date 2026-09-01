@extends('errors.layout')
@section('code','400')@section('code_label','Bad Request')@section('icon','bi-exclamation-circle-fill')@section('badge','Permintaan Tidak Valid')
@section('title','Permintaan Tidak Valid')
@section('message','Permintaan Anda tidak dapat diproses. Periksa kembali data yang dikirim dan coba lagi.')
@section('hint','Pastikan format data benar dan tidak ada field yang kosong.')
@section('action')
<a href="{{ url()->previous() }}" class="btn btn-ghost"><i class="bi bi-arrow-left"></i> Kembali</a>
<a href="{{ url('/') }}" class="btn btn-primary"><i class="bi bi-house"></i> Beranda</a>
@endsection
