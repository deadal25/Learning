@extends('layouts.app')

@section('title', 'Katalog Mata Pelajaran - Musashi Learning')

@section('content')
<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.8rem; margin-bottom: 0.35rem;">Mata Pelajaran Musashi</h1>
    <p style="color: var(--text-muted); font-size: 0.95rem;">
        Pilih mata pelajaran untuk melihat materi presentasi slide dan mengerjakan 10 butir soal latihan.
    </p>
</div>

<div class="grid grid-cols-3">
    @foreach($subjects as $subject)
        @php
            $currentLevelId = $userProgressMap[$subject->id] ?? null;
            $currentLevel = $subject->levels->firstWhere('id', $currentLevelId) ?? $subject->levels->first();
            $points = $userPointsMap[$subject->id] ?? 0;
        @endphp
        <div class="card" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div class="card-header" style="background: {{ $subject->badge_color }}10;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 44px; height: 44px; border-radius: var(--radius-md); background: {{ $subject->badge_color }}; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; font-weight: 800;">
                            {{ substr($subject->name, 0, 1) }}
                        </div>
                        <div>
                            <h3 style="font-size: 1.2rem; margin-bottom: 2px;">{{ $subject->name }}</h3>
                            <span class="badge" style="background: {{ $subject->badge_color }}20; color: {{ $subject->badge_color }};">
                                {{ $subject->levels->count() }} Level Tingkatan
                            </span>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 1.25rem; min-height: 54px;">
                        {{ $subject->description }}
                    </p>

                    <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 12px; margin-bottom: 1.25rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <span style="font-size: 0.8rem; font-weight: 600; color: #475569;">Level Aktif:</span>
                            <span class="badge badge-primary">{{ $currentLevel?->name ?? 'Level 1' }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <span style="font-size: 0.8rem; font-weight: 600; color: #475569;">Poin Level:</span>
                            <span style="font-weight: 700; color: {{ $points >= 100 ? '#059669' : 'var(--color-primary)' }};">
                                {{ $points }} / 100 Poin
                            </span>
                        </div>
                        <div class="progress-container">
                            <div class="progress-bar {{ $points >= 100 ? 'progress-bar-success' : '' }}" style="width: {{ $points }}%;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <a href="{{ route('student.subjects.show', $subject) }}" class="btn btn-primary" style="width: 100%;">
                    Buka Modul Belajar &rarr;
                </a>
            </div>
        </div>
    @endforeach
</div>
@endsection
