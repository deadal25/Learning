@extends('layouts.app')

@section('title', 'Latihan Soal: ' . $level->subject->name . ' - ' . $level->name)

@section('content')
<div style="margin-bottom: 1.5rem;">
    <a href="{{ route('student.exercises.index') }}" class="btn btn-secondary btn-sm" style="margin-bottom: 0.75rem;">
        &larr; Kembali ke Daftar Pertemuan (1 - 25)
    </a>
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                <span class="badge" style="background: {{ $level->subject->badge_color }}15; color: {{ $level->subject->badge_color }};">
                    {{ $level->subject->name }}
                </span>
                <span class="badge badge-primary">{{ $level->name }}</span>
            </div>
            <h1 style="font-size: 1.8rem; margin: 0;">Latihan Soal {{ $level->name }}</h1>
        </div>
        <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: var(--radius-md); padding: 8px 16px; text-align: right;">
            <div style="font-size: 0.75rem; color: #1e40af; font-weight: 700; text-transform: uppercase;">Aturan Poin</div>
            <div style="font-size: 0.95rem; font-weight: 800; color: #1e40af;">
                +10 Poin Tiap Jawaban Benar &bull; Target: 100 Poin
            </div>
        </div>
    </div>
</div>

<form action="{{ route('student.exercises.submit', $level) }}" method="POST" id="exerciseForm">
    @csrf

    <div style="display: flex; flex-direction: column; gap: 1.5rem; margin-bottom: 2rem;">
        @forelse($exercises as $index => $exercise)
            <div class="card" id="q_{{ $exercise->id }}" style="border-left: 4px solid var(--color-primary);">
                <div class="card-header" style="background: #f8fafc;">
                    <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span class="badge badge-primary" style="font-size: 0.85rem; padding: 4px 10px;">
                                Soal {{ $index + 1 }} dari {{ $exercises->count() }}
                            </span>
                            <span style="font-size: 0.82rem; color: var(--text-muted); font-weight: 600;">
                                Nilai: +{{ $exercise->points }} Poin
                            </span>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div style="font-size: 1.15rem; font-weight: 700; color: #0f172a; margin-bottom: 1.25rem; line-height: 1.5;">
                        {{ $exercise->question }}
                    </div>

                    <!-- Options Radio Cards -->
                    <div>
                        @foreach(['a' => $exercise->option_a, 'b' => $exercise->option_b, 'c' => $exercise->option_c, 'd' => $exercise->option_d] as $letter => $optionText)
                            <label class="quiz-option-card">
                                <input type="radio" name="answers[{{ $exercise->id }}]" value="{{ $letter }}" onchange="updateAnswerCount()">
                                <div class="quiz-option-label">
                                    <div class="option-letter">{{ strtoupper($letter) }}</div>
                                    <div style="color: #1e293b;">{{ $optionText }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                    Soal latihan untuk level ini sedang dipersiapkan.
                </div>
            </div>
        @endforelse
    </div>

    @if($exercises->isNotEmpty())
        <div class="card" style="position: sticky; bottom: 1.5rem; z-index: 50; box-shadow: 0 -4px 16px rgba(0,0,0,0.1); border: 2px solid var(--color-primary);">
            <div class="card-body" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; padding: 1rem 1.5rem;">
                <div>
                    <div style="font-weight: 700; font-size: 0.95rem; color: #0f172a;">
                        Status Pengerjaan: <span id="answeredCount" style="color: var(--color-primary);">0</span> / {{ $exercises->count() }} Soal Terjawab
                    </div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);">
                        Pastikan semua soal terjawab untuk mendapatkan poin maksimal (100 Poin).
                    </div>
                </div>
                <div style="display: flex; gap: 10px;">
                    <a href="{{ route('student.exercises.index') }}" class="btn btn-secondary">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg" onclick="return confirm('Apakah Anda yakin ingin mengirimkan seluruh jawaban ini?')">
                        Kirim Jawaban & Hitung Skor &rarr;
                    </button>
                </div>
            </div>
        </div>
    @endif
</form>

@push('scripts')
<script>
    function updateAnswerCount() {
        const radios = document.querySelectorAll('#exerciseForm input[type="radio"]:checked');
        document.getElementById('answeredCount').textContent = radios.length;
    }

    document.addEventListener('DOMContentLoaded', updateAnswerCount);
</script>
@endpush
@endsection
