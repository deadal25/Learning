@extends('layouts.app')

@section('title', 'Kelola Soal: ' . $test->title)

@section('content')
<div style="margin-bottom: 2rem;">
    <a href="{{ route('admin.japanese.tests.index') }}" class="btn btn-secondary btn-sm" style="margin-bottom: 0.75rem;">
        &larr; Kembali ke Daftar Tes Evaluasi
    </a>
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                <span class="badge" style="background: #fdf2f8; color: #db2777; font-weight: 800;">
                    Bank Soal Tes Evaluasi
                </span>
                <span class="badge badge-primary">
                    Pertemuan {{ $test->start_meeting }} - {{ $test->end_meeting }}
                </span>
                @if($test->is_active)
                    <span class="badge" style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; font-weight: 800;">
                        🟢 Status: Aktif di Siswa
                    </span>
                @else
                    <span class="badge" style="background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1; font-weight: 700;">
                        ⚪ Status: Nonaktif (Terkunci)
                    </span>
                @endif
            </div>
            <h1 style="font-size: 1.8rem; margin: 0; font-weight: 800; color: #0f172a;">
                {{ $test->title }}
            </h1>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 4px;">
                Kelola butir soal pilihan ganda (A, B, C, D), kunci jawaban, dan aktifkan tes ini kapan saja jika siswa sudah siap mengikuti evaluasi.
            </p>
        </div>
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <!-- Activation Toggle Button -->
            <form action="{{ route('admin.japanese.tests.toggle-active', $test) }}" method="POST" style="margin: 0;" @if($test->is_active) onsubmit="return confirm('Nonaktifkan ujian evaluasi ini? Siswa tidak akan dapat melihat atau mengakses tes ini lagi.')" @endif>
                @csrf
                @if($test->is_active)
                    <button type="submit" class="btn btn-sm btn-secondary" style="font-weight: 700; color: #b91c1c; background: #fef2f2; border: 1px solid #fecaca; display: flex; align-items: center; gap: 6px;">
                        <span>⏸</span> Nonaktifkan Ujian
                    </button>
                @else
                    <button type="submit" class="btn btn-sm" style="font-weight: 700; color: #ffffff; background: #059669; border: 1px solid #059669; display: flex; align-items: center; gap: 6px;">
                        <span>▶</span> Aktifkan Ujian untuk Siswa
                    </button>
                @endif
            </form>

            <button type="button" class="btn btn-primary" onclick="openAddQuestionModal()" style="font-weight: 700; background: #db2777; border-color: #db2777;">
                + Tambah Soal Tes Baru
            </button>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 1.5rem;">
        {{ session('success') }}
    </div>
@endif

<div class="card" style="box-shadow: var(--shadow-sm); border-radius: var(--radius-lg); overflow: hidden;">
    <div class="card-header" style="background: #ffffff; padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-size: 1.15rem; margin: 0; font-weight: 800; color: #0f172a;">
            Daftar Butir Soal (Total {{ $test->questions->count() }} Soal)
        </h3>
        <span class="badge badge-neutral">Standar Kelulusan: {{ $test->pass_score }}%</span>
    </div>

    <div class="card-body" style="padding: 1.5rem;">
        @forelse($test->questions as $q)
            <div class="card" style="margin-bottom: 1.25rem; border: 1px solid #e2e8f0; border-radius: var(--radius-md); box-shadow: none;">
                <div class="card-header" style="background: #f8fafc; padding: 0.75rem 1.25rem; display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span class="badge" style="background: #db2777; color: #ffffff; font-weight: 800;">
                            Soal #{{ $q->question_number }}
                        </span>
                        <span class="badge badge-neutral" style="font-size: 0.75rem;">
                            Bobot: {{ $q->points }} Poin
                        </span>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick='openEditQuestionModal(@json($q))'>
                            Edit
                        </button>
                        <form action="{{ route('admin.japanese.tests.questions.destroy', $q) }}" method="POST" onsubmit="return confirm('Hapus soal ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card-body" style="padding: 1.25rem;">
                    <h4 style="font-size: 1.05rem; margin: 0 0 1rem; color: #0f172a; font-weight: 700; line-height: 1.45;">
                        {{ $q->question }}
                    </h4>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 1rem;">
                        <div style="padding: 8px 12px; border-radius: var(--radius-sm); border: 1px solid {{ $q->correct_option === 'a' ? '#10b981' : '#e2e8f0' }}; background: {{ $q->correct_option === 'a' ? '#ecfdf5' : '#ffffff' }}; font-size: 0.88rem;">
                            <strong>A.</strong> {{ $q->option_a }}
                            @if($q->correct_option === 'a') <span style="color: #059669; font-weight: 800; margin-left: 6px;">(Kunci Jawaban)</span> @endif
                        </div>
                        <div style="padding: 8px 12px; border-radius: var(--radius-sm); border: 1px solid {{ $q->correct_option === 'b' ? '#10b981' : '#e2e8f0' }}; background: {{ $q->correct_option === 'b' ? '#ecfdf5' : '#ffffff' }}; font-size: 0.88rem;">
                            <strong>B.</strong> {{ $q->option_b }}
                            @if($q->correct_option === 'b') <span style="color: #059669; font-weight: 800; margin-left: 6px;">(Kunci Jawaban)</span> @endif
                        </div>
                        <div style="padding: 8px 12px; border-radius: var(--radius-sm); border: 1px solid {{ $q->correct_option === 'c' ? '#10b981' : '#e2e8f0' }}; background: {{ $q->correct_option === 'c' ? '#ecfdf5' : '#ffffff' }}; font-size: 0.88rem;">
                            <strong>C.</strong> {{ $q->option_c }}
                            @if($q->correct_option === 'c') <span style="color: #059669; font-weight: 800; margin-left: 6px;">(Kunci Jawaban)</span> @endif
                        </div>
                        <div style="padding: 8px 12px; border-radius: var(--radius-sm); border: 1px solid {{ $q->correct_option === 'd' ? '#10b981' : '#e2e8f0' }}; background: {{ $q->correct_option === 'd' ? '#ecfdf5' : '#ffffff' }}; font-size: 0.88rem;">
                            <strong>D.</strong> {{ $q->option_d }}
                            @if($q->correct_option === 'd') <span style="color: #059669; font-weight: 800; margin-left: 6px;">(Kunci Jawaban)</span> @endif
                        </div>
                    </div>

                    @if($q->explanation)
                        <div style="font-size: 0.82rem; color: #475569; background: #f8fafc; padding: 8px 12px; border-radius: var(--radius-sm); border-left: 3px solid #db2777;">
                            <strong>💡 Pembahasan:</strong> {{ $q->explanation }}
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div style="text-align: center; padding: 3rem; color: var(--text-muted);">
                <div style="font-size: 2.5rem; margin-bottom: 8px;">📝</div>
                <h4 style="color: #0f172a; margin-bottom: 4px;">Belum Ada Soal Ujian</h4>
                <p style="font-size: 0.88rem; max-width: 440px; margin: 0 auto 1.5rem;">
                    Silakan tambahkan butir-butir soal evaluasi per 4 pertemuan untuk tes evaluasi ini.
                </p>
                <button type="button" class="btn btn-primary" onclick="openAddQuestionModal()">
                    + Tambah Soal Pertama
                </button>
            </div>
        @endforelse
    </div>
</div>

<!-- Modal: Tambah Soal -->
<div id="addQuestionModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; padding: 1rem; overflow-y: auto;">
    <div style="background: #ffffff; border-radius: var(--radius-lg); max-width: 600px; width: 100%; box-shadow: var(--shadow-xl); overflow: hidden; margin: 2rem auto;">
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 1.2rem; margin: 0; color: #0f172a; font-weight: 800;">+ Tambah Soal Tes Baru</h3>
            <button type="button" onclick="closeAddQuestionModal()" style="background:none; border:none; font-size: 1.4rem; cursor: pointer;">&times;</button>
        </div>

        <form action="{{ route('admin.japanese.tests.questions.store', $test) }}" method="POST">
            @csrf
            <div style="padding: 1.5rem; max-height: 75vh; overflow-y: auto;">
                <div class="form-group">
                    <label class="form-label" style="font-weight: 700;">Pertanyaan Soal *</label>
                    <textarea name="question" class="form-control" rows="3" required placeholder="Tuliskan pertanyaan tes..."></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 1rem;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 700;">Pilihan A *</label>
                        <input type="text" name="option_a" class="form-control" required placeholder="Teks pilihan A">
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 700;">Pilihan B *</label>
                        <input type="text" name="option_b" class="form-control" required placeholder="Teks pilihan B">
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 700;">Pilihan C *</label>
                        <input type="text" name="option_c" class="form-control" required placeholder="Teks pilihan C">
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 700;">Pilihan D *</label>
                        <input type="text" name="option_d" class="form-control" required placeholder="Teks pilihan D">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 1rem;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 700;">Kunci Jawaban Benar *</label>
                        <select name="correct_option" class="form-control" required style="font-weight: 700;">
                            <option value="a">A</option>
                            <option value="b">B</option>
                            <option value="c">C</option>
                            <option value="d">D</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 700;">Poin Soal *</label>
                        <input type="number" name="points" class="form-control" value="25" min="1" max="100" required>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-weight: 700;">Pembahasan Jawaban (Opsional)</label>
                    <textarea name="explanation" class="form-control" rows="2" placeholder="Penjelasan singkat mengapa jawaban tersebut benar..."></textarea>
                </div>
            </div>

            <div style="padding: 1rem 1.5rem; background: #f8fafc; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary" onclick="closeAddQuestionModal()">Batal</button>
                <button type="submit" class="btn btn-primary" style="font-weight: 700; background: #db2777; border-color: #db2777;">Simpan Soal</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Soal -->
<div id="editQuestionModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; padding: 1rem; overflow-y: auto;">
    <div style="background: #ffffff; border-radius: var(--radius-lg); max-width: 600px; width: 100%; box-shadow: var(--shadow-xl); overflow: hidden; margin: 2rem auto;">
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 1.2rem; margin: 0; color: #0f172a; font-weight: 800;">Edit Butir Soal</h3>
            <button type="button" onclick="closeEditQuestionModal()" style="background:none; border:none; font-size: 1.4rem; cursor: pointer;">&times;</button>
        </div>

        <form id="editQuestionForm" action="" method="POST">
            @csrf
            @method('PUT')
            <div style="padding: 1.5rem; max-height: 75vh; overflow-y: auto;">
                <div class="form-group">
                    <label class="form-label" style="font-weight: 700;">Pertanyaan Soal *</label>
                    <textarea name="question" id="edit_question" class="form-control" rows="3" required></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 1rem;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 700;">Pilihan A *</label>
                        <input type="text" name="option_a" id="edit_option_a" class="form-control" required>
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 700;">Pilihan B *</label>
                        <input type="text" name="option_b" id="edit_option_b" class="form-control" required>
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 700;">Pilihan C *</label>
                        <input type="text" name="option_c" id="edit_option_c" class="form-control" required>
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 700;">Pilihan D *</label>
                        <input type="text" name="option_d" id="edit_option_d" class="form-control" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 1rem;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 700;">Kunci Jawaban Benar *</label>
                        <select name="correct_option" id="edit_correct_option" class="form-control" required style="font-weight: 700;">
                            <option value="a">A</option>
                            <option value="b">B</option>
                            <option value="c">C</option>
                            <option value="d">D</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 700;">Poin Soal *</label>
                        <input type="number" name="points" id="edit_points" class="form-control" min="1" max="100" required>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-weight: 700;">Pembahasan Jawaban (Opsional)</label>
                    <textarea name="explanation" id="edit_explanation" class="form-control" rows="2"></textarea>
                </div>
            </div>

            <div style="padding: 1rem 1.5rem; background: #f8fafc; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary" onclick="closeEditQuestionModal()">Batal</button>
                <button type="submit" class="btn btn-primary" style="font-weight: 700;">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openAddQuestionModal() {
    document.getElementById('addQuestionModal').style.display = 'flex';
}
function closeAddQuestionModal() {
    document.getElementById('addQuestionModal').style.display = 'none';
}

function openEditQuestionModal(q) {
    document.getElementById('edit_question').value = q.question;
    document.getElementById('edit_option_a').value = q.option_a;
    document.getElementById('edit_option_b').value = q.option_b;
    document.getElementById('edit_option_c').value = q.option_c;
    document.getElementById('edit_option_d').value = q.option_d;
    document.getElementById('edit_correct_option').value = q.correct_option.toLowerCase();
    document.getElementById('edit_points').value = q.points;
    document.getElementById('edit_explanation').value = q.explanation || '';
    document.getElementById('editQuestionForm').action = "{{ url('admin/japanese-test-questions') }}/" + q.id;
    document.getElementById('editQuestionModal').style.display = 'flex';
}
function closeEditQuestionModal() {
    document.getElementById('editQuestionModal').style.display = 'none';
}
</script>
@endpush
@endsection
