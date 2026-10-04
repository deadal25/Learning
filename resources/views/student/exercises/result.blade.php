@extends('layouts.app')

@section('title', 'Hasil Latihan Soal - ' . $level->name)

@section('content')
<div style="margin-bottom: 2rem;">
    <a href="{{ route('student.exercises.index') }}" class="btn btn-secondary btn-sm" style="margin-bottom: 0.75rem;">
        &larr; Kembali ke Daftar Pertemuan (1 - 25)
    </a>
    <h1 style="font-size: 1.8rem; margin: 0; font-weight: 800; color: #0f172a;">
        Hasil Latihan: {{ $level->name }}
    </h1>
    <p style="color: var(--text-muted); font-size: 0.92rem; margin-top: 4px;">
        {{ $level->subject->name ?? 'Bahasa Inggris' }} &bull; {{ $exercises->count() }} Butir Soal (+10 Poin Tiap Jawaban Benar)
    </p>
</div>

<!-- Score Announcement Card -->
<div class="card" style="margin-bottom: 2rem; border: 2px solid {{ $totalScore >= 70 ? '#10b981' : '#818cf8' }}; box-shadow: var(--shadow-md);">
    <div class="card-body" style="padding: 2.5rem; text-align: center;">
        <div style="font-size: 3.5rem; margin-bottom: 0.5rem;">
            @if($totalScore >= 100)
                🏆
            @elseif($totalScore >= 70)
                🎉
            @else
                📚
            @endif
        </div>

        <div style="font-size: 0.85rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">
            Poin yang Berhasil Kamu Raih:
        </div>

        <div style="font-size: 3.5rem; font-weight: 800; font-family: var(--font-heading); color: {{ $totalScore >= 70 ? '#059669' : 'var(--color-primary)' }}; line-height: 1.1; margin: 0.5rem 0;">
            {{ $totalScore }} <span style="font-size: 1.5rem; color: var(--text-muted); font-weight: 600;">/ 100 PTS</span>
        </div>

        <div style="max-width: 500px; margin: 0 auto 1.5rem;">
            <div class="progress-container" style="height: 16px; background: #e2e8f0; border-radius: 9999px; overflow: hidden;">
                <div class="progress-bar {{ $totalScore >= 70 ? 'progress-bar-success' : '' }}" style="width: {{ $totalScore }}%; height: 100%; background: {{ $totalScore >= 70 ? '#10b981' : '#2563eb' }}; transition: width 0.3s ease;"></div>
            </div>
        </div>

        <!-- Next Meeting Unlocked Notification Card -->
        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: var(--radius-lg); padding: 1.5rem; max-width: 620px; margin: 0 auto 1.5rem;">
            <h3 style="color: #065f46; font-size: 1.3rem; margin-bottom: 0.5rem;">
                {{ $totalScore >= 70 ? '🎉 Latihan Selesai dengan Baik!' : '📝 Latihan ' . $level->name . ' Berhasil Diselesaikan!' }}
            </h3>

            @if($nextLevel)
                <p style="color: #047857; font-size: 0.95rem; margin-bottom: 1.25rem;">
                    Anda telah menyelesaikan latihan untuk <strong>{{ $level->name }}</strong>. Soal untuk <strong>{{ $nextLevel->name }}</strong> sekarang sudah otomatis terbuka dan siap dikerjakan!
                </p>

                <div style="display: flex; justify-content: center; gap: 10px; flex-wrap: wrap;">
                    <a href="{{ route('student.exercises.show', $nextLevel) }}" class="btn btn-primary btn-lg" style="padding: 12px 24px; font-weight: 800;">
                        🚀 Lanjut Kerjakan {{ $nextLevel->name }} &rarr;
                    </a>
                    <a href="{{ route('student.exercises.index') }}" class="btn btn-secondary btn-lg" style="padding: 12px 24px;">
                        📋 Daftar Pertemuan (1 - 25)
                    </a>
                </div>
            @else
                <p style="color: #047857; font-size: 0.95rem; margin-bottom: 1.25rem;">
                    🏅 Selamat! Anda telah menamatkan seluruh 25 pertemuan latihan Bahasa Inggris!
                </p>
                <a href="{{ route('student.exercises.index') }}" class="btn btn-primary btn-lg" style="padding: 12px 24px; font-weight: 800;">
                    📋 Kembali ke Daftar Pertemuan
                </a>
            @endif
        </div>

        <div style="display: flex; justify-content: center; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('student.exercises.show', $level) }}" class="btn btn-secondary">
                🔄 Ulangi Latihan {{ $level->name }}
            </a>
            <a href="{{ route('student.materials.index') }}" class="btn btn-secondary">
                📖 Pelajari Slide Materi
            </a>
        </div>
    </div>
</div>

<!-- Detailed Question Breakdown -->
<h2 style="font-size: 1.35rem; margin-bottom: 1rem; font-weight: 800; color: #0f172a;">
    Ulasan Jawaban & Pembahasan Detail Soal
</h2>

<div style="display: flex; flex-direction: column; gap: 1rem;">
    @foreach($results as $index => $res)
        @php
            $ex = $res['exercise'];
            $isCorrect = $res['is_correct'];
            $userChoice = $res['user_choice'];
        @endphp
        <div class="card" style="border-left: 4px solid {{ $isCorrect ? '#10b981' : '#e11d48' }};">
            <div class="card-body">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 10px;">
                    <div>
                        <span class="badge {{ $isCorrect ? 'badge-success' : 'badge-danger' }}" style="margin-bottom: 6px;">
                            {{ $isCorrect ? '✓ Jawaban Benar (+10 Poin)' : '✗ Jawaban Salah (0 Poin)' }}
                        </span>
                        <h4 style="font-size: 1.05rem; margin: 0; color: #0f172a;">
                            {{ $index + 1 }}. {{ $ex->question }}
                        </h4>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 8px; margin: 12px 0;">
                    @foreach(['a', 'b', 'c', 'd'] as $opt)
                        @php
                            $optKey = 'option_' . $opt;
                            $isUserPick = ($userChoice === $opt);
                            $isAnswer = ($ex->correct_option === $opt);
                        @endphp
                        <div style="padding: 8px 12px; border-radius: var(--radius-sm); border: 1px solid {{ $isAnswer ? '#10b981' : ($isUserPick ? '#ef4444' : '#e2e8f0') }}; background: {{ $isAnswer ? '#ecfdf5' : ($isUserPick ? '#fef2f2' : '#ffffff') }}; font-size: 0.88rem;">
                            <strong>{{ strtoupper($opt) }}.</strong> {{ $ex->$optKey }}
                            @if($isAnswer)
                                <span style="color: #059669; font-weight: 700; margin-left: 4px;">✓ (Kunci)</span>
                            @endif
                            @if($isUserPick && !$isAnswer)
                                <span style="color: #dc2626; font-weight: 700; margin-left: 4px;">(Pilihanmu)</span>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if($ex->explanation)
                    <div style="background: #f8fafc; border: 1px dashed var(--border-color); border-radius: var(--radius-sm); padding: 10px 14px; font-size: 0.85rem; color: #475569;">
                        💡 <strong>Penjelasan Guru:</strong> {{ $ex->explanation }}
                    </div>
                @endif
            </div>
        </div>
    @endforeach
</div>

<div style="margin-top: 2rem; text-align: center;">
    <a href="{{ route('student.exercises.index') }}" class="btn btn-secondary">
        &larr; Kembali ke Daftar Pertemuan (1 - 25)
    </a>
</div>
@endsection
