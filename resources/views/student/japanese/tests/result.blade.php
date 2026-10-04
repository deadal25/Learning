@extends('layouts.app')

@section('title', 'Hasil Evaluasi: ' . $test->title)

@section('content')
<div style="max-width: 800px; margin: 1.5rem auto 3rem;">
    <!-- Result Header Card -->
    <div class="card" style="box-shadow: var(--shadow-md); border-radius: var(--radius-xl); text-align: center; padding: 2.5rem 2rem; margin-bottom: 2rem; border-top: 6px solid {{ $submission->is_passed ? '#10b981' : '#ef4444' }};">
        <div style="font-size: 3.5rem; margin-bottom: 8px;">
            {{ $submission->is_passed ? '🎉' : '📚' }}
        </div>
        
        <h1 style="font-size: 1.8rem; margin: 0 0 6px; color: #0f172a; font-weight: 800;">
            {{ $submission->is_passed ? 'Selamat! Anda Berhasil Lulus' : 'Evaluasi Belum Mencapai Standar' }}
        </h1>
        
        <p style="color: var(--text-muted); font-size: 0.95rem; margin: 0 auto 1.5rem; max-width: 520px;">
            {{ $test->title }} &bull; Materi Pertemuan {{ $test->start_meeting }} s/d {{ $test->end_meeting }}
        </p>

        <div style="display: inline-flex; align-items: center; justify-content: center; gap: 2rem; background: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 1.25rem 2.5rem; margin-bottom: 1.5rem;">
            <div>
                <div style="font-size: 0.78rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Skor Akhir</div>
                <div style="font-size: 2.75rem; font-weight: 900; color: {{ $submission->is_passed ? '#10b981' : '#ef4444' }}; font-family: monospace; line-height: 1;">
                    {{ $submission->score }}
                </div>
            </div>
            <div style="border-left: 2px solid #e2e8f0; height: 50px;"></div>
            <div>
                <div style="font-size: 0.78rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Jawaban Benar</div>
                <div style="font-size: 1.85rem; font-weight: 800; color: #0f172a; line-height: 1.2;">
                    {{ $submission->correct_count }} / {{ $submission->total_questions }}
                </div>
            </div>
            <div style="border-left: 2px solid #e2e8f0; height: 50px;"></div>
            <div>
                <div style="font-size: 0.78rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Passing Grade</div>
                <div style="font-size: 1.85rem; font-weight: 800; color: #2563eb; line-height: 1.2;">
                    {{ $test->pass_score }}%
                </div>
            </div>
        </div>

        @if($submission->teacher_feedback)
            <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: var(--radius-md); padding: 12px 18px; max-width: 580px; margin: 0 auto 1.5rem; font-size: 0.9rem; color: #1e40af;">
                <strong>Catatan Evaluasi:</strong> <em>"{{ $submission->teacher_feedback }}"</em>
            </div>
        @endif

        <div style="display: flex; justify-content: center; gap: 12px; flex-wrap: wrap;">
            <a href="{{ route('student.japanese.grades.index') }}" class="btn btn-primary" style="font-weight: 700;">
                📊 Buka Riwayat Nilai Saya &rarr;
            </a>
            <a href="{{ route('student.japanese.tests.show', $test) }}" class="btn btn-secondary" style="font-weight: 700;">
                🔄 Ulangi Tes Ini
            </a>
            <a href="{{ route('student.japanese.tests.index') }}" class="btn btn-secondary">
                Daftar Tes Lainnya
            </a>
        </div>
    </div>

    <!-- Review Answers Breakdown -->
    <h2 style="font-size: 1.35rem; color: #0f172a; font-weight: 800; margin-bottom: 1rem;">
        📝 Pembahasan Butir Soal Tes
    </h2>

    @php
        $reviewQuestions = (isset($questions) && $questions->isNotEmpty()) ? $questions : $test->questions;
    @endphp

    @foreach($reviewQuestions as $idx => $q)
        @php
            $userAns = strtolower($submittedAnswers[$q->id] ?? '');
            $correctAns = strtolower($q->correct_option);
            $isCorrect = ($userAns === $correctAns);
        @endphp
        <div class="card" style="margin-bottom: 1.25rem; border: 1px solid {{ $isCorrect ? '#a7f3d0' : '#fecaca' }}; border-radius: var(--radius-md); box-shadow: none;">
            <div class="card-header" style="background: {{ $isCorrect ? '#f0fdf4' : '#fef2f2' }}; padding: 0.75rem 1.25rem; display: flex; justify-content: space-between; align-items: center;">
                <span style="font-weight: 800; font-size: 0.9rem; color: {{ $isCorrect ? '#065f46' : '#991b1b' }};">
                    {{ $isCorrect ? '✅ Jawaban Anda Benar (+'.$q->points.' Poin)' : '❌ Jawaban Anda Salah (0 Poin)' }}
                </span>
                <span class="badge badge-neutral" style="font-size: 0.75rem;">
                    Soal #{{ $idx + 1 }}
                </span>
            </div>

            <div class="card-body" style="padding: 1.25rem;">
                <h4 style="font-size: 1.05rem; margin: 0 0 10px; color: #0f172a; font-weight: 700;">
                    {{ $q->question }}
                </h4>

                <div style="font-size: 0.9rem; margin-bottom: 8px;">
                    <div>Jawaban Anda: <strong style="color: {{ $isCorrect ? '#059669' : '#dc2626' }};">{{ strtoupper($userAns ?: '-') }}. {{ $q->{'option_' . $userAns} ?? '-' }}</strong></div>
                    @if(!$isCorrect)
                        <div style="margin-top: 4px;">Kunci Jawaban yang Benar: <strong style="color: #059669;">{{ strtoupper($correctAns) }}. {{ $q->{'option_' . $correctAns} }}</strong></div>
                    @endif
                </div>

                @if($q->explanation)
                    <div style="font-size: 0.82rem; color: #475569; background: #f8fafc; padding: 8px 12px; border-radius: var(--radius-sm); border-left: 3px solid #db2777; margin-top: 10px;">
                        <strong>💡 Pembahasan:</strong> {{ $q->explanation }}
                    </div>
                @endif
            </div>
        </div>
    @endforeach
</div>
@endsection
