@php
    $item = $j ?? $m ?? null;
    $val = function ($key, $default = '') use ($item) {
        if (($v = old($key)) !== null) {
            return $v;
        }
        $raw = $item ? ($item[$key] ?? $default) : $default;
        // Normalisasi jam dari "H:i:s" menjadi "H:i" agar <input type="time"> valid.
        if (in_array($key, ['jam_mulai', 'jam_selesai'], true) && is_string($raw) && $raw !== '') {
            return \Illuminate\Support\Str::substr($raw, 0, 5);
        }
        return $raw;
    };
@endphp
<div class="row g-3">
    <div class="col-12">
        <label class="form-label small fw-bold">Mata Pelajaran</label>
        <select name="mata_pelajaran_id" class="form-select" required>
            <option value="">-- Pilih Mapel --</option>
            @foreach($mapels as $mp)
                <option value="{{ $mp->id }}" @selected((string) $val('mata_pelajaran_id') === (string) $mp->id)>{{ $mp->nama }} ({{ $mp->kode }})</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label small fw-bold">Kelas</label>
        <select name="kelas_id" class="form-select" required>
            <option value="">-- Pilih Kelas --</option>
            @foreach($kelases as $k)
                <option value="{{ $k->id }}" @selected((string) $val('kelas_id') === (string) $k->id)>{{ $k->nama }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label small fw-bold">Guru</label>
        <select name="guru_id" class="form-select" required>
            <option value="">-- Pilih Guru --</option>
            @foreach($gurus as $g)
                <option value="{{ $g->id }}" @selected((string) $val('guru_id') === (string) $g->id)>{{ $g->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label small fw-bold">Hari</label>
        <select name="hari" class="form-select" required>
            @foreach(['senin','selasa','rabu','kamis','jumat','sabtu'] as $h)
                <option value="{{ $h }}" @selected($val('hari') === $h)>{{ ucfirst($h) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label small fw-bold">Ruangan</label>
        <input type="text" name="ruangan" value="{{ $val('ruangan') }}" class="form-control" placeholder="cth: Lab Komputer 1" required>
    </div>
    <div class="col-md-6">
        <label class="form-label small fw-bold">Jam Mulai</label>
        <input type="time" name="jam_mulai" value="{{ $val('jam_mulai') }}" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label class="form-label small fw-bold">Jam Selesai</label>
        <input type="time" name="jam_selesai" value="{{ $val('jam_selesai') }}" class="form-control" required>
    </div>
</div>
