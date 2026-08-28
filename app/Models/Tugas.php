<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tugas extends Model
{
    protected $table = 'tugas';

    protected $fillable = ['user_id', 'kelas_id', 'mata_pelajaran_id', 'judul', 'deskripsi', 'lampiran', 'lampiran_nama', 'batas_pengumpulan', 'tipe', 'form_data'];

    protected function casts(): array
    {
        return [
            'batas_pengumpulan' => 'date',
            'form_data' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class, 'mata_pelajaran_id');
    }

    public function pengumpulan(): HasMany
    {
        return $this->hasMany(PengumpulanTugas::class);
    }

    public function isForm(): bool
    {
        return $this->tipe === 'form';
    }

    /**
     * Deadline is inclusive of the due date (valid until the end of that day).
     * Using isPast() on a date-cast value would mark "today" as expired at 00:00:01.
     */
    public function isExpired(): bool
    {
        return $this->batas_pengumpulan !== null && $this->batas_pengumpulan->lt(today());
    }

    public function isOpen(): bool
    {
        return ! $this->isExpired();
    }

    /**
     * @return array{key: string, label: string, tone: string}
     */
    public function deadlineStatus(): array
    {
        if ($this->batas_pengumpulan === null) {
            return ['key' => 'open', 'label' => 'Tanpa batas', 'tone' => 'muted'];
        }

        $days = (int) today()->startOfDay()->diffInDays($this->batas_pengumpulan->copy()->startOfDay(), false);

        if ($days < 0) {
            $late = abs($days);

            return ['key' => 'expired', 'label' => 'Terlambat '.$late.' hari', 'tone' => 'danger'];
        }

        if ($days === 0) {
            return ['key' => 'today', 'label' => 'Jatuh tempo hari ini', 'tone' => 'warning'];
        }

        if ($days === 1) {
            return ['key' => 'soon', 'label' => 'Besok', 'tone' => 'warning'];
        }

        if ($days <= 3) {
            return ['key' => 'soon', 'label' => $days.' hari lagi', 'tone' => 'warning'];
        }

        return ['key' => 'ok', 'label' => $days.' hari lagi', 'tone' => 'primary'];
    }

    public function questions(): array
    {
        $data = $this->form_data;

        if (is_string($data)) {
            $data = json_decode($data, true) ?: [];
        }

        return is_array($data) ? $data : [];
    }
}
