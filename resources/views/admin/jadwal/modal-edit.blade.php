<div class="modal fade" id="editModal{{ $j->id }}" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.jadwal.update', $j) }}" class="modal-content">
            @csrf @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Edit Jadwal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @include('admin.jadwal.fields', ['j' => $j, 'materi' => null])
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
