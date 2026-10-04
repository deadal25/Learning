@extends('layouts.app')

@section('title', 'Latihan Soal Bahasa Inggris (Pertemuan 1 - 25) - Musashi')

@section('content')
<div style="margin-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px; flex-wrap: wrap;">
                <span class="badge" style="background: #eff6ff; color: #1e40af; font-weight: 800; font-size: 0.78rem;">
                    Bahasa Inggris
                </span>
                <span class="badge" style="background: #f1f5f9; color: #334155; font-weight: 700; font-size: 0.78rem;">
                    Kurikulum 25 Pertemuan
                </span>
                <span class="badge" style="background: #ecfdf5; color: #065f46; font-weight: 800; font-size: 0.78rem; border: 1px solid #a7f3d0;">
                    🔓 Sistem Buka Bertahap
                </span>
            </div>
            <h1 style="font-size: 1.85rem; margin: 0; font-weight: 800; color: #0f172a;">
                📚 Latihan Soal Bahasa Inggris (Pertemuan 1 - 25)
            </h1>
            <p style="color: var(--text-muted); font-size: 0.92rem; margin-top: 4px;">
                Selesaikan latihan soal per pertemuan secara bertahap. Soal pertemuan berikutnya akan terbuka otomatis setelah Anda menyelesaikan pertemuan sebelumnya.
            </p>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('student.materials.index') }}" class="btn btn-secondary btn-sm">
                📖 Buka Materi Slide &rarr;
            </a>
            <a href="{{ route('student.grades.index') }}" class="btn btn-secondary btn-sm">
                📊 Riwayat Skor Latihan
            </a>
        </div>
    </div>
</div>

<!-- Progress Statistics Cards -->
<div class="grid grid-cols-3" style="gap: 1.25rem; margin-bottom: 2rem;">
    <!-- Stat 1: Completed Meetings -->
    <div class="card" style="border-left: 4px solid #10b981; box-shadow: var(--shadow-sm); border-radius: var(--radius-md);">
        <div class="card-body" style="padding: 1.25rem;">
            <div style="font-size: 0.76rem; font-weight: 700; color: #065f46; text-transform: uppercase; letter-spacing: 0.5px;">
                Progres Pertemuan Tuntas
            </div>
            <div style="font-size: 1.85rem; font-weight: 800; color: #10b981; margin-top: 4px;">
                {{ $completedMeetingsCount }} <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted);">/ {{ $levels->count() }} Pertemuan</span>
            </div>
            <div class="progress-container" style="height: 8px; margin-top: 8px; background: #e2e8f0; border-radius: 9999px; overflow: hidden;">
                <div style="height: 100%; width: {{ round(($completedMeetingsCount / max(1, $levels->count())) * 100) }}%; background: #10b981; transition: width 0.3s ease;"></div>
            </div>
        </div>
    </div>

    <!-- Stat 2: Total Accumulated Points -->
    <div class="card" style="border-left: 4px solid #2563eb; box-shadow: var(--shadow-sm); border-radius: var(--radius-md);">
        <div class="card-body" style="padding: 1.25rem;">
            <div style="font-size: 0.76rem; font-weight: 700; color: #1e40af; text-transform: uppercase; letter-spacing: 0.5px;">
                Total Poin Terkumpul
            </div>
            <div style="font-size: 1.85rem; font-weight: 800; color: #2563eb; margin-top: 4px;">
                {{ number_format($totalPoints) }} <span style="font-size: 0.85rem; font-weight: 600; color: #1d4ed8;">Poin</span>
            </div>
            <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 4px;">
                Dari seluruh latihan pertemuan yang diselesaikan
            </div>
        </div>
    </div>

    <!-- Stat 3: Current Active Meeting -->
    <div class="card" style="border-left: 4px solid #f59e0b; box-shadow: var(--shadow-sm); border-radius: var(--radius-md);">
        <div class="card-body" style="padding: 1.25rem;">
            <div style="font-size: 0.76rem; font-weight: 700; color: #b45309; text-transform: uppercase; letter-spacing: 0.5px;">
                Pertemuan Aktif Saat Ini
            </div>
            <div style="font-size: 1.5rem; font-weight: 800; color: #d97706; margin-top: 4px;">
                {{ $activeLevel ? $activeLevel->name : 'Pertemuan 1' }}
            </div>
            @if($activeLevel)
                <div style="margin-top: 6px;">
                    <a href="{{ route('student.exercises.show', $activeLevel) }}" style="font-size: 0.82rem; font-weight: 700; color: #b45309; text-decoration: underline;">
                        Lanjut Kerjakan Sekarang &rarr;
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- 25 Meetings Grid -->
<div class="grid grid-cols-3" style="gap: 1.25rem; margin-bottom: 2.5rem;">
    @foreach($levels as $lvl)
        @php
            $st = $statuses->get($lvl->id);
            $pts = $st ? (int)$st->points : 0;
            $isCompleted = isset($completedOrders[$lvl->order]);
            $isUnlocked = $lvl->is_active && ($st ? (bool)$st->is_unlocked : ($lvl->order === 1));
            $accentColor = !$lvl->is_active ? '#cbd5e1' : ($isCompleted ? '#10b981' : ($isUnlocked ? '#2563eb' : '#94a3b8'));
        @endphp

        <div class="card" style="box-shadow: var(--shadow-sm); border-radius: var(--radius-lg); border-top: 4px solid {{ $accentColor }}; display: flex; flex-direction: column; justify-content: space-between; background: {{ !$lvl->is_active ? '#f8fafc' : ($isUnlocked ? '#ffffff' : '#fcfcfd') }};">
            <div class="card-body" style="padding: 1.35rem;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; gap: 8px;">
                    <span class="badge" style="background: {{ !$lvl->is_active ? '#f1f5f9' : ($isCompleted ? '#ecfdf5' : ($isUnlocked ? '#eff6ff' : '#f1f5f9')) }}; color: {{ !$lvl->is_active ? '#64748b' : ($isCompleted ? '#065f46' : ($isUnlocked ? '#1e40af' : '#64748b')) }}; font-weight: 800; font-size: 0.82rem;">
                        🗓️ {{ $lvl->name }}
                    </span>

                    @if(!$lvl->is_active)
                        <span class="badge" style="background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; font-weight: 800; font-size: 0.74rem;">
                            🔒 Belum Diaktifkan Super Admin
                        </span>
                    @elseif($isCompleted)
                        <span class="badge" style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; font-weight: 800; font-size: 0.74rem;">
                            ✅ Selesai ({{ $pts }} Pts)
                        </span>
                    @elseif($isUnlocked)
                        <span class="badge" style="background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; font-weight: 800; font-size: 0.74rem;">
                            🟢 Siap Dikerjakan
                        </span>
                    @else
                        <span class="badge" style="background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1; font-weight: 700; font-size: 0.74rem;">
                            🔒 Terkunci
                        </span>
                    @endif
                </div>

                <h3 style="font-size: 1.05rem; margin: 0 0 6px 0; color: {{ $lvl->is_active ? '#0f172a' : '#64748b' }}; font-weight: 800; line-height: 1.35;">
                    Latihan Soal {{ $lvl->name }}
                </h3>

                <p style="font-size: 0.84rem; color: var(--text-muted); line-height: 1.45; margin-bottom: 1rem;">
                    {{ $lvl->description ?: 'Uji pemahaman dan penguasaan materi pembelajaran ' . $lvl->name . '.' }}
                </p>

                @if(!$lvl->is_active)
                    <div style="background: #fffbeb; border: 1px solid #fde68a; color: #92400e; font-size: 0.76rem; padding: 6px 10px; border-radius: 6px; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 6px;">
                        <span>🔒</span>
                        <span><strong>Belum Dibuka:</strong> Latihan ini dinonaktifkan oleh Super Admin dan belum dapat dikerjakan.</span>
                    </div>
                @endif

                <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 8px 12px; display: flex; justify-content: space-between; font-size: 0.82rem; margin-bottom: 0.4rem;">
                    <span style="color: #64748b;">Jumlah Soal:</span>
                    <strong style="color: #0f172a;">{{ $lvl->exercises_count ?: 10 }} Butir Soal</strong>
                </div>

                <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 8px 12px; display: flex; justify-content: space-between; font-size: 0.82rem;">
                    <span style="color: #64748b;">Skor Terbaik:</span>
                    <strong style="color: {{ $pts >= 70 ? '#059669' : ($pts > 0 ? '#2563eb' : '#64748b') }};">
                        {{ $pts }} / 100 Poin
                    </strong>
                </div>
            </div>

            <div class="card-footer" style="background: #ffffff; padding: 1rem 1.35rem; border-top: 1px solid var(--border-color);">
                @if(!$lvl->is_active)
                    <button type="button" class="btn btn-secondary btn-sm" disabled style="width: 100%; text-align: center; opacity: 0.65; cursor: not-allowed; font-size: 0.78rem; background: #f8fafc; color: #94a3b8; border: 1px solid #e2e8f0;">
                        🔒 Belum Diaktifkan Super Admin
                    </button>
                @elseif($isUnlocked)
                    <a href="{{ route('student.exercises.show', $lvl) }}" class="btn {{ $isCompleted ? 'btn-secondary' : 'btn-primary' }} btn-sm" style="width: 100%; text-align: center; font-weight: 800; display: block;">
                        @if($isCompleted)
                            🔄 Ulangi Latihan ({{ $pts }} Pts)
                        @elseif($pts > 0)
                            ▶ Lanjutkan Latihan
                        @else
                            ✍️ Mulai Kerjakan Latihan &rarr;
                        @endif
                    </a>
                @else
                    <button type="button" class="btn btn-secondary btn-sm" disabled style="width: 100%; text-align: center; opacity: 0.65; cursor: not-allowed; font-size: 0.78rem;">
                        🔒 Selesaikan Pertemuan {{ $lvl->order - 1 }} Dulu
                    </button>
                @endif
            </div>
        </div>
    @endforeach
</div>
@endsection
