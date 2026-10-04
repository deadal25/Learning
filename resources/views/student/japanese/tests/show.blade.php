@extends('layouts.app')

@section('title', 'Pengerjaan ' . $test->title)

@section('content')
<div style="max-width: 800px; margin: 1.5rem auto 3rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <a href="{{ route('student.japanese.tests.index') }}" class="btn btn-secondary btn-sm" style="margin-bottom: 0.5rem;">
                &larr; Kembali ke Daftar Soal
            </a>
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                <span class="badge" style="background: #fdf2f8; color: #db2777; font-weight: 800; font-size: 0.8rem;">
                    {{ $test->category === 'per_pertemuan' ? '📝 Latihan Soal Pertemuan ' . $test->start_meeting : '🏆 Ujian Evaluasi Pertemuan ' . $test->start_meeting . '-' . $test->end_meeting }}
                </span>
                <span class="badge" style="background: #eff6ff; color: #1d4ed8; font-weight: 700; font-size: 0.78rem;">
                    🔀 Soal Diacak ({{ $questions->count() }} Nomor)
                </span>
            </div>
            <h1 style="font-size: 1.65rem; margin: 0; font-weight: 800; color: #0f172a;">
                {{ $test->title }}
            </h1>
            <p style="color: var(--text-muted); font-size: 0.88rem; margin: 4px 0 0;">
                {{ $test->description ?: 'Pilih jawaban yang paling tepat untuk setiap pertanyaan di bawah ini.' }}
            </p>
        </div>

        <div style="background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 100%); color: #ffffff; border-radius: var(--radius-md); padding: 10px 18px; text-align: center; border: 1px solid rgba(255,255,255,0.1);">
            <div style="font-size: 0.72rem; color: #94a3b8; font-weight: 700; text-transform: uppercase;">Durasi Pengerjaan</div>
            <div style="font-family: monospace; font-size: 1.25rem; font-weight: 800; color: #38bdf8;">
                {{ $test->duration_minutes }} Menit
            </div>
        </div>
    </div>

    @if($previousSubmission)
        <div class="alert alert-info" style="margin-bottom: 1.5rem; border-left: 4px solid #3b82f6;">
            ℹ️ Anda sebelumnya telah mengerjakan latihan ini dengan perolehan skor: <strong>{{ $previousSubmission->score }}/100</strong> (Status: {{ $previousSubmission->is_passed ? 'Lulus' : 'Belum Lulus' }}). Soal disajikan secara <strong>acak</strong> pada setiap pengerjaan.
        </div>
    @endif

    <form action="{{ route('student.japanese.tests.submit', $test) }}" method="POST" id="testForm">
        @csrf

        @foreach($questions as $idx => $q)
            <input type="hidden" name="question_ids[]" value="{{ $q->id }}">

            <div class="card" style="box-shadow: var(--shadow-sm); border-radius: var(--radius-lg); margin-bottom: 1.5rem; border: 1px solid #e2e8f0;">
                <div class="card-header" style="background: #f8fafc; padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span class="badge" style="background: #db2777; color: #ffffff; font-weight: 800; font-size: 0.82rem;">
                            Nomor {{ $idx + 1 }} dari {{ $questions->count() }}
                        </span>
                    </div>
                    <span class="badge badge-neutral" style="font-size: 0.75rem;">
                        {{ $q->points }} Poin
                    </span>
                </div>

                <div class="card-body" style="padding: 1.5rem;">
                    <h3 style="font-size: 1.15rem; color: #0f172a; margin: 0 0 1.25rem; font-weight: 700; line-height: 1.5;">
                        {{ $q->question }}
                    </h3>

                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <!-- Option A -->
                        <label class="test-option-label" style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: var(--radius-md); cursor: pointer; transition: all 0.2s;">
                            <input type="radio" name="answers[{{ $q->id }}]" value="a" required style="accent-color: #db2777; transform: scale(1.2);">
                            <span style="font-weight: 700; color: #64748b; width: 22px;">A.</span>
                            <span style="font-size: 0.95rem; color: #1e293b;">{{ $q->option_a }}</span>
                        </label>

                        <!-- Option B -->
                        <label class="test-option-label" style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: var(--radius-md); cursor: pointer; transition: all 0.2s;">
                            <input type="radio" name="answers[{{ $q->id }}]" value="b" required style="accent-color: #db2777; transform: scale(1.2);">
                            <span style="font-weight: 700; color: #64748b; width: 22px;">B.</span>
                            <span style="font-size: 0.95rem; color: #1e293b;">{{ $q->option_b }}</span>
                        </label>

                        <!-- Option C -->
                        <label class="test-option-label" style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: var(--radius-md); cursor: pointer; transition: all 0.2s;">
                            <input type="radio" name="answers[{{ $q->id }}]" value="c" required style="accent-color: #db2777; transform: scale(1.2);">
                            <span style="font-weight: 700; color: #64748b; width: 22px;">C.</span>
                            <span style="font-size: 0.95rem; color: #1e293b;">{{ $q->option_c }}</span>
                        </label>

                        <!-- Option D -->
                        <label class="test-option-label" style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: var(--radius-md); cursor: pointer; transition: all 0.2s;">
                            <input type="radio" name="answers[{{ $q->id }}]" value="d" required style="accent-color: #db2777; transform: scale(1.2);">
                            <span style="font-weight: 700; color: #64748b; width: 22px;">D.</span>
                            <span style="font-size: 0.95rem; color: #1e293b;">{{ $q->option_d }}</span>
                        </label>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="card" style="padding: 1.5rem; background: #ffffff; border-radius: var(--radius-lg); box-shadow: var(--shadow-md); text-align: center;">
            <p style="font-size: 0.92rem; color: var(--text-muted); margin-bottom: 1.25rem;">
                Pastikan Anda telah memeriksa seluruh jawaban Anda sebelum menekan tombol kirim di bawah.
            </p>
            <button type="submit" class="btn btn-primary" style="padding: 12px 36px; font-weight: 800; font-size: 1.05rem; background: #db2777; border-color: #db2777; box-shadow: 0 4px 12px rgba(219,39,119,0.3);">
                Kirim Jawaban & Lihat Hasil Tes &rarr;
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.querySelectorAll('.test-option-label input[type="radio"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const name = this.name;
        document.querySelectorAll(`input[name="${name}"]`).forEach(r => {
            r.closest('.test-option-label').style.borderColor = '#e2e8f0';
            r.closest('.test-option-label').style.background = '#ffffff';
        });
        if (this.checked) {
            this.closest('.test-option-label').style.borderColor = '#db2777';
            this.closest('.test-option-label').style.background = '#fdf2f8';
        }
    });
});
</script>
@endpush
@endsection
