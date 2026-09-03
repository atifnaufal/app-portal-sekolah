<?php

namespace App\Helpers;

use App\Models\SchoolFeature;
use App\Models\Setting;
use Illuminate\Support\Facades\Schema;

/**
 * Fitur per-sekolah (dikelola Admin Pusat) dengan fallback ke flag global.
 *
 * Efektif = baris school_features jika ada, selain itu flag global `feature_*`
 * (default true). Registrasi guru/siswa memakai kolom schools.reg_*_open
 * sebagai sumber utama (lihat registrationOpen()).
 */
class FeatureHelper
{
    public const KEYS = [
        ['key' => 'feature_spp_enabled', 'label' => 'Manajemen SPP', 'description' => 'Sistem pembayaran dan tagihan siswa', 'icon' => 'bi-cash-stack', 'category' => 'Keuangan'],
        ['key' => 'feature_lms_enabled', 'label' => 'Learning Management System', 'description' => 'Mata pelajaran, materi, tugas, dan nilai', 'icon' => 'bi-mortarboard', 'category' => 'Academy'],
        ['key' => 'feature_eskul_enabled', 'label' => 'Ekstrakurikuler', 'description' => 'Manajemen eskul dan anggota', 'icon' => 'bi-people', 'category' => 'Academy'],
        ['key' => 'feature_perpustakaan_enabled', 'label' => 'Perpustakaan Digital', 'description' => 'Katalog buku dan kategori', 'icon' => 'bi-book', 'category' => 'Academy'],
        ['key' => 'feature_jadwal_enabled', 'label' => 'Jadwal Pelajaran', 'description' => 'Penjadwalan guru dan kelas', 'icon' => 'bi-calendar3', 'category' => 'Academy'],
        ['key' => 'feature_nilai_enabled', 'label' => 'Manajemen Nilai', 'description' => 'Input dan export nilai siswa', 'icon' => 'bi-graph-up', 'category' => 'Academy'],
        ['key' => 'feature_absensi_enabled', 'label' => 'Absensi', 'description' => 'Rekap kehadiran siswa', 'icon' => 'bi-person-check', 'category' => 'Academy'],
        ['key' => 'feature_berita_enabled', 'label' => 'Portal Berita', 'description' => 'Pengumuman dan portal berita global', 'icon' => 'bi-megaphone', 'category' => 'Konten'],
        ['key' => 'feature_registration_guru_enabled', 'label' => 'Registrasi Guru', 'description' => 'Pendaftaran akun guru baru', 'icon' => 'bi-person-badge', 'category' => 'Registrasi'],
        ['key' => 'feature_registration_siswa_enabled', 'label' => 'Registrasi Siswa', 'description' => 'Pendaftaran akun siswa baru', 'icon' => 'bi-person-avatar', 'category' => 'Registrasi'],
        ['key' => 'feature_raport_enabled', 'label' => 'Raport & Rapor', 'description' => 'Pembuatan raport semester', 'icon' => 'bi-file-earmark-text', 'category' => 'Academy'],
        ['key' => 'feature_communication_enabled', 'label' => 'Komunikasi', 'description' => 'Chat dan notifikasi real-time', 'icon' => 'bi-chat-dots', 'category' => 'Komunikasi'],
    ];

    public static function keys(): array
    {
        return array_column(self::KEYS, 'key');
    }

    /** Status efektif fitur untuk satu sekolah (per-sekolah > global > default true). */
    protected static array $memo = [];

    public static function forSchool(?int $schoolId, string $key, bool $default = true): bool
    {
        $memoKey = ($schoolId ?? 0).'|'.$key;
        if (array_key_exists($memoKey, self::$memo)) {
            return self::$memo[$memoKey];
        }

        $result = $default;
        if ($schoolId) {
            $row = SchoolFeature::where('school_id', $schoolId)->where('feature_key', $key)->first(['is_enabled']);
            if ($row) {
                $result = (bool) $row->is_enabled;
            } else {
                $global = Setting::getValue($key, null);
                $result = $global === null ? $default : (bool) $global;
            }
        } else {
            $global = Setting::getValue($key, null);
            $result = $global === null ? $default : (bool) $global;
        }

        return self::$memo[$memoKey] = $result;
    }

    public static function setForSchool(int $schoolId, string $key): bool
    {
        abort_unless(in_array($key, self::keys(), true), 400);

        $row = SchoolFeature::firstOrNew(['school_id' => $schoolId, 'feature_key' => $key]);
        $row->is_enabled = ! (bool) ($row->exists ? $row->is_enabled : self::forSchool($schoolId, $key));
        $row->save();

        return (bool) $row->is_enabled;
    }

    /** Pendaftaran role untuk sekolah: kolom schools > fallback global setting. */
    public static function registrationOpen(?int $schoolId, string $role): bool
    {
        if ($schoolId && Schema::hasColumn('schools', 'reg_guru_open')) {
            $school = \App\Models\School::find($schoolId, ['id', 'reg_guru_open', 'reg_siswa_open', 'is_active']);
            if ($school && $school->is_active) {
                return $role === 'guru' ? (bool) $school->reg_guru_open : (bool) $school->reg_siswa_open;
            }
            return false;
        }

        return (bool) Setting::getValue("registration_{$role}_enabled", false);
    }
}
