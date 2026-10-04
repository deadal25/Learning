@extends('layouts.app')

@section('title', $subject->name . ' - Jenjang Level Belajar')

@section('content')
<div style="margin-bottom: 1.5rem;">
    <a href="{{ route('student.subjects.index') }}" class="btn btn-secondary btn-sm" style="margin-bottom: 0.75rem;">
        &larr; Kembali ke Katalog Mapel
    </a>
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 44px; height: 44px; border-radius: var(--radius-md); background: {{ $subject->badge_color }}; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; font-weight: 800;">
                    {{ substr($subject->name, 0, 1) }}
                </div>
                <div>
                    <h1 style="font-size: 1.9rem; margin: 0;">{{ $subject->name }}</h1>
                    <span style="color: var(--text-muted); font-size: 0.92rem;">Jenjang Pembelajaran Bertingkat Berbasis Level</span>
                </div>
            </div>
        </div>
        <div>
            <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 8px 16px; text-align: right;">
                <div style="font-size: 0.75rem; color: #64748b; font-weight: 700; text-transform: uppercase;">Poin Level Aktif:</div>
                <div style="font-size: 1.4rem; font-weight: 800; color: {{ ($userProgress?->current_points ?? 0) >= 100 ? '#059669' : 'var(--color-primary)' }};">
                    {{ $userProgress?->current_points ?? 0 }} / 100 Pts
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Levels Roadmap Cards -->
<div class="level-path">
    @foreach($subject->levels as $level)
        @php
            $isUnlocked = in_array($level->id, $unlockedLevelIds);
            $isCompleted = in_array($level->id, $completedLevelIds);
            $points = $levelScores[$level->id] ?? 0;
            $canUnlockNext = ($points >= 100);
            $nextLevel = $level->nextLevel();
            $isCurrent = ($userProgress?->current_level_id === $level->id);
        @endphp

        <div class="level-card {{ $isCompleted ? 'completed' : ($isCurrent ? 'current' : ($isUnlocked ? 'unlocked' : 'locked')) }}">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.25rem;">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <div style="width: 48px; height: 48px; border-radius: var(--radius-full); background: {{ $isCompleted ? '#10b981' : ($isUnlocked ? 'var(--color-primary)' : '#cbd5e1') }}; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; font-weight: 800;">
                        @if($isCompleted)
                            ✓
                        @elseif($isUnlocked)
                            {{ $level->order }}
                        @else
                            🔒
                        @endif
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <h2 style="font-size: 1.35rem; margin: 0; color: {{ $isUnlocked ? '#0f172a' : '#64748b' }};">
                                {{ $level->name }}
                            </h2>
                            @if($isCurrent)
                                <span class="badge badge-primary">Level Aktif Saat Ini</span>
                            @endif
                            @if($isCompleted)
                                <span class="badge badge-success">Selesai (100 Pts)</span>
                            @endif
                        </div>
                        <p style="font-size: 0.9rem; color: var(--text-muted); margin-top: 4px;">
                            {{ $level->description }}
                        </p>
                    </div>
                </div>

                <div>
                    @if($isUnlocked)
                        <div style="text-align: right;">
                            <div style="font-size: 0.78rem; color: #64748b; font-weight: 700; text-transform: uppercase;">Skor Tertinggi Level Ini</div>
                            <div style="font-size: 1.3rem; font-weight: 800; color: {{ $points >= 100 ? '#059669' : 'var(--color-primary)' }};">
                                {{ $points }} / 100 Poin
                            </div>
                        </div>
                    @else
                        <span class="badge badge-neutral" style="font-size: 0.82rem;">
                            🔒 Terkunci
                        </span>
                    @endif
                </div>
            </div>

            <!-- Materials Section (Always open and visible for students to study) -->
            <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1.25rem; margin-bottom: 1.25rem;">
                <div style="font-weight: 700; font-size: 0.95rem; color: #334155; margin-bottom: 10px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <span>📊</span> Materi Pembelajaran (Slide Presentasi PPT, PDF & Modul)
                    </div>
                    <span class="badge badge-success" style="font-size: 0.72rem;">Terbuka untuk Dipelajari</span>
                </div>

                @if($level->materials->isNotEmpty())
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        @foreach($level->materials as $mat)
                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: #f8fafc; border-radius: var(--radius-md); border: 1px solid #e2e8f0; flex-wrap: wrap; gap: 10px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <span style="font-size: 1.3rem;">
                                        {{ $mat->file_icon }}
                                    </span>
                                    <div>
                                        <strong style="color: #0f172a; font-size: 0.92rem;">{{ $mat->title }}</strong>
                                        <div style="font-size: 0.78rem; color: var(--text-muted);">
                                            {{ $mat->file_type_label }}
                                            @if($mat->description)
                                                &bull; {{ $mat->description }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div style="display: flex; gap: 8px;">
                                    <a href="{{ route('student.materials.view', $mat) }}" class="btn btn-secondary btn-sm">
                                        Buka & Pelajari Slide &rarr;
                                    </a>
                                    @if($mat->file_path)
                                        <a href="{{ route('student.materials.download', $mat) }}" class="btn btn-secondary btn-sm" title="Unduh File">
                                            ⬇ Unduh
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="font-size: 0.85rem; color: var(--text-muted); font-style: italic; background: #f8fafc; padding: 10px 14px; border-radius: var(--radius-md); border: 1px dashed #cbd5e1;">
                        Materi slide untuk tingkatan ini sedang dipersiapkan oleh pengajar.
                    </div>
                @endif
            </div>

            <!-- Exercise & Progression CTA -->
            @if($isUnlocked)
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: var(--radius-md); padding: 1.25rem;">
                    <div>
                        <div style="font-weight: 700; font-size: 1rem; color: #1e1b4b; display: flex; align-items: center; gap: 8px;">
                            <span>🎯</span> 10 Soal Latihan Level {{ $level->order }}
                        </div>
                        <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 2px;">
                            Setiap jawaban benar bernilai <strong>+10 poin</strong>. Capai 100 poin untuk membuka level selanjutnya!
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        <a href="{{ route('student.exercises.show', $level) }}" class="btn btn-primary">
                            {{ $points > 0 ? 'Kerjakan Ulang Latihan' : 'Mulai Kerjakan 10 Soal Latihan' }} &rarr;
                        </a>

                        @if($canUnlockNext && $nextLevel)
                            <form action="{{ route('student.exercises.unlock-next', $level) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-level-up">
                                    🚀 Naik ke {{ $nextLevel->name }}!
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @else
                <!-- Locked Notice for Exercise Only -->
                <div style="background: #f1f5f9; border: 1px dashed #cbd5e1; border-radius: var(--radius-md); padding: 1.25rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <div style="font-size: 2rem; color: #94a3b8;">🔒</div>
                        <div>
                            <strong style="color: #475569; font-size: 0.95rem;">Latihan Soal Level Masih Terkunci</strong>
                            <p style="font-size: 0.85rem; color: #64748b; margin: 2px 0 0;">
                                Anda tetap dapat mempelajari semua materi di atas. Untuk membuka 10 soal kuis level ini, selesaikan latihan di level sebelumnya dan kumpulkan total <strong>100 poin</strong>.
                            </p>
                        </div>
                    </div>
                    <span class="badge badge-neutral">Kuis Terkunci</span>
                </div>
            @endif
        </div>
    @endforeach
</div>
@endsection
