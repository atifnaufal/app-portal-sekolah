<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengumpulanTugas extends Model
{
    protected $table = 'pengumpulan_tugas';
    protected $fillable = ['tugas_id', 'siswa_id', 'status', 'nilai', 'feedback_guru', 'revisi_aktif', 'catatan', 'jawaban_file', 'jawaban_nama', 'jawaban_form', 'dikumpulkan_pada', 'dinilai_pada'];
    protected function casts(): array { return ['dikumpulkan_pada' => 'datetime', 'dinilai_pada' => 'datetime', 'revisi_aktif' => 'boolean', 'nilai' => 'decimal:2', 'jawaban_form' => 'json']; }
    public function tugas(): BelongsTo { return $this->belongsTo(Tugas::class); }
    public function siswa(): BelongsTo { return $this->belongsTo(User::class, 'siswa_id'); }
}
