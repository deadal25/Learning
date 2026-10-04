@extends('layouts.app')

@section('title', 'Laporan Progres Siswa: ' . $student->name)

@section('content')
<div style="margin-bottom: 1.5rem;">
    <a href="{{ route('admin.students.index') }}" class="btn btn-secondary btn-sm" style="margin-bottom: 0.75rem;">
        &larr; Kembali ke Daftar Siswa
    </a>
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-size: 1.8rem; margin-bottom: 0.25rem;">Rapor & Progres Belajar: {{ $student->name }}</h1>
            <p style="color: var(--text-muted); font-size: 0.92rem;">
                Email: {{ $student->email }} &bull; Terdaftar: {{ $student->created_at->format('d M Y') }} &bull; Didaftarkan oleh: {{ $student->creator?->name ?? 'Admin' }}
            </p>
        </div>
        <div>
            <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-secondary">
                Edit Profil Siswa
            </a>
        </div>
    </div>
</div>

<div class="grid grid-cols-3" style="margin-bottom: 2rem;">
    @foreach($subjects as $subj)
        @php
            $userProg = $student->progresses->firstWhere('subject_id', $subj->id);
            $currentLevel = $userProg?->currentLevel;
            $currentPoints = $userProg?->current_points ?? 0;
        @endphp
        <div class="card">
            <div class="card-header" style="background: {{ $subj->badge_color }}10;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 36px; height: 36px; border-radius: var(--radius-sm); background: {{ $subj->badge_color }}; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                        {{ substr($subj->name, 0, 1) }}
                    </div>
                    <div>
                        <h3 style="font-size: 1.05rem; margin: 0;">{{ $subj->name }}</h3>
                        <span style="font-size: 0.75rem; color: #475569;">Level Aktif: <strong>{{ $currentLevel?->name ?? 'Level 1' }}</strong></span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div style="margin-bottom: 1rem;">
                    <div style="display: flex; justify-content: space-between; font-size: 0.85rem; margin-bottom: 4px; font-weight: 600;">
                        <span>Poin Level Saat Ini:</span>
                        <span style="color: var(--color-primary);">{{ $currentPoints }} / 100 Poin</span>
                    </div>
                    <div class="progress-container">
                        <div class="progress-bar {{ $currentPoints >= 100 ? 'progress-bar-success' : '' }}" style="width: {{ $currentPoints }}%;"></div>
                    </div>
                </div>

                <div style="border-top: 1px solid var(--border-color); padding-top: 1rem;">
                    <div style="font-size: 0.82rem; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">
                        Tingkatan & Status:
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        @foreach($subj->levels as $lvl)
                            @php
                                $status = $student->levelStatuses->firstWhere('level_id', $lvl->id);
                                $isUnlocked = $status?->is_unlocked ?? false;
                                $isCompleted = $status?->is_completed ?? false;
                                $points = $status?->points ?? 0;
                            @endphp
                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 6px 10px; border-radius: var(--radius-sm); background: {{ $isCompleted ? '#f0fdf4' : ($isUnlocked ? '#f8fafc' : '#f1f5f9') }}; border: 1px solid {{ $isCompleted ? '#bbf7d0' : ($isUnlocked ? '#e2e8f0' : '#e2e8f0') }};">
                                <div style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem;">
                                    <span>
                                        @if($isCompleted)
                                            ✅
                                        @elseif($isUnlocked)
                                            🔓
                                        @else
                                            🔒
                                        @endif
                                    </span>
                                    <span style="font-weight: {{ $isUnlocked ? '600' : '400' }}; color: {{ $isUnlocked ? '#0f172a' : '#94a3b8' }};">
                                        {{ $lvl->name }}
                                    </span>
                                </div>
                                <span class="badge {{ $isCompleted ? 'badge-success' : ($isUnlocked ? 'badge-primary' : 'badge-neutral') }}" style="font-size: 0.72rem;">
                                    {{ $isCompleted ? 'Selesai (100 pts)' : ($isUnlocked ? $points . ' pts' : 'Terkunci') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
