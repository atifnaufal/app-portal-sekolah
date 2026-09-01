@extends('errors.layout')
@section('code','503')@section('code_label','Maintenance')@section('icon','bi-tools')@section('badge','Pemeliharaan')
@section('title','Sementara Tidak Tersedia')
@section('message','Sistem sedang dalam pemeliharaan terjadwal. Kami akan kembali segera. Terima kasih atas pengertiannya.')
@section('hint','Coba muat ulang dalam beberapa menit. Jika di jam kerja, hubungi admin untuk info jadwal.')
@section('action')
<a href="{{ url('/') }}" onclick="location.reload();return false" class="btn btn-primary"><i class="bi bi-arrow-clockwise"></i> Muat Ulang</a>
@endsection
