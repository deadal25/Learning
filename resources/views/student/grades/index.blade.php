@extends('layouts.app')

@section('title', 'Riwayat Skor Latihan Soal - Siswa Musashi')

@section('content')
<div style="margin-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div class="badge badge-primary" style="margin-bottom: 6px;">
                🎓 Kelas: {{ $user->class_name ?: 'Bahasa Inggris' }} &bull; Divisi: {{ $user->division ?: 'Umum' }}
            </div>
            <h1 style="font-size: 1.85rem; margin: 0; font-weight: 800; color: #0f172a;">
                📊 Riwayat Skor Latihan Soal
            </h1>
            <p style="color: var(--text-muted); font-size: 0.92rem; margin-top: 4px;">
                Pantau perolehan skor nilai dari latihan soal pada setiap pertemuan dan level pembelajaran {{ $subject->name ?? 'Bahasa Inggris' }}.
            </p>
        </div>
        <a href="{{ route('student.dashboard') }}" class="btn btn-secondary btn-sm">
            &larr; Kembali ke Beranda
        </a>
    </div>
</div>

<!-- Stat Cards -->
<div class="grid grid-cols-3" style="gap: 1.25rem; margin-bottom: 2rem;">
    <div class="card" style="border-left: 4px solid #2563eb; box-shadow: var(--shadow-sm); border-radius: var(--radius-md);">
        <div class="card-body" style="padding: 1.25rem;">
            <div style="font-size: 0.76rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Total Poin Latihan</div>
            <div style="font-size: 1.85rem; font-weight: 800; color: #2563eb; margin-top: 4px;">
                {{ number_format($totalPoints) }} <span style="font-size: 0.85rem; font-weight: 600; color: #1d4ed8;">Poin</span>
            </div>
            <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 2px;">Akumulasi nilai latihan soal</div>
        </div>
    </div>

    <div class="card" style="border-left: 4px solid #10b981; box-shadow: var(--shadow-sm); border-radius: var(--radius-md);">
        <div class="card-body" style="padding: 1.25rem;">
            <div style="font-size: 0.76rem; font-weight: 700; color: #065f46; text-transform: uppercase; letter-spacing: 0.5px;">Latihan Tuntas (100 Poin)</div>
            <div style="font-size: 1.85rem; font-weight: 800; color: #10b981; margin-top: 4px;">
                {{ $completedLevels }} <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted);">/ {{ $totalLevels }} Pertemuan</span>
            </div>
            <div style="font-size: 0.78rem; color: #059669; margin-top: 2px;">Latihan berhasil diselesaikan sempurna</div>
        </div>
    </div>

    <div class="card" style="border-left: 4px solid #f59e0b; box-shadow: var(--shadow-sm); border-radius: var(--radius-md);">
        <div class="card-body" style="padding: 1.25rem;">
            <div style="font-size: 0.76rem; font-weight: 700; color: #b45309; text-transform: uppercase; letter-spacing: 0.5px;">Rata-Rata Skor Dikerjakan</div>
            <div style="font-size: 1.85rem; font-weight: 800; color: #d97706; margin-top: 4px;">
                {{ $averageScore }} <span style="font-size: 0.85rem; font-weight: 600; color: #b45309;">/ 100 Poin</span>
            </div>
            <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 2px;">Dari {{ $attemptedCount }} level/pertemuan yang dicoba</div>
        </div>
    </div>
</div>

<!-- Main Table: Exercise Score History -->
<div class="card" style="box-shadow: var(--shadow-sm); border-radius: var(--radius-lg); overflow: hidden; border: 1px solid #e2e8f0;">
    <div class="card-header" style="background: #ffffff; padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0;">
        <div>
            <h3 style="font-size: 1.15rem; margin: 0; font-weight: 800; color: #0f172a;">
                Daftar Perolehan Skor Latihan Soal Per Pertemuan
            </h3>
            <p style="font-size: 0.82rem; color: var(--text-muted); margin: 2px 0 0;">
                Skor langsung tercatat otomatis saat Anda menyelesaikan latihan soal pada masing-masing modul.
            </p>
        </div>
        <span class="badge" style="background: #eff6ff; color: #1e40af; font-weight: 700;">
            {{ $subject->name ?? 'Bahasa Inggris' }}
        </span>
    </div>

    <div class="table-responsive">
        <table class="table" style="vertical-align: middle; margin-bottom: 0;">
            <thead>
                <tr style="background: #f8fafc; font-weight: 800; font-size: 0.82rem; color: #475569;">
                    <th style="width: 50px; text-align: center;">No</th>
                    <th>Pertemuan / Level</th>
                    <th style="text-align: center;">Jumlah Soal</th>
                    <th style="text-align: center;">Jawaban Benar</th>
                    <th style="text-align: center;">Skor Latihan</th>
                    <th style="text-align: center;">Status</th>
                    <th style="text-align: right; width: 170px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($levels as $index => $lvl)
                    @php
                        $st = $statuses->get($lvl->id);
                        $pts = $st ? (int)$st->points : 0;
                        $isCompleted = $st && ($st->is_completed || $pts >= 100);
                        $isUnlocked = $st ? $st->is_unlocked : ($index === 0);
                        $att = $attempts->get($lvl->id);
                    @endphp
                    <tr>
                        <td style="text-align: center; color: #64748b; font-weight: 600;">
                            {{ $index + 1 }}
                        </td>
                        <td>
                            <div style="font-weight: 800; color: #0f172a; font-size: 0.95rem;">
                                {{ $lvl->name }}
                            </div>
                            @if($lvl->description)
                                <div style="font-size: 0.78rem; color: var(--text-muted);">{{ Str::limit($lvl->description, 60) }}</div>
                            @endif
                        </td>
                        <td style="text-align: center; font-weight: 600; color: #475569;">
                            {{ $lvl->exercises_count }} Soal
                        </td>
                        <td style="text-align: center; font-weight: 600; color: #334155;">
                            @if($att)
                                <span style="color: #10b981; font-weight: 700;">{{ $att->correct_attempts }}</span> / {{ $att->total_attempts }} jawaban
                            @else
                                <span style="color: var(--text-muted);">-</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            @if($pts >= 100)
                                <span class="badge" style="background: #dcfce7; color: #15803d; font-weight: 900; font-size: 0.92rem; padding: 4px 10px;">
                                    💯 100 Poin
                                </span>
                            @elseif($pts >= 70)
                                <span class="badge" style="background: #e0f2fe; color: #0369a1; font-weight: 800; font-size: 0.92rem; padding: 4px 10px;">
                                    ⭐ {{ $pts }} Poin
                                </span>
                            @elseif($pts > 0)
                                <span class="badge" style="background: #fef3c7; color: #b45309; font-weight: 800; font-size: 0.92rem; padding: 4px 10px;">
                                    {{ $pts }} Poin
                                </span>
                            @else
                                <span class="badge badge-neutral" style="font-size: 0.82rem;">
                                    0 Poin
                                </span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            @if($isCompleted)
                                <span class="badge badge-success" style="font-size: 0.78rem; font-weight: 700;">
                                    ✅ Tuntas
                                </span>
                            @elseif($pts > 0)
                                <span class="badge" style="background: #eff6ff; color: #2563eb; font-size: 0.78rem; font-weight: 700;">
                                    🔄 Sedang Belajar
                                </span>
                            @elseif($isUnlocked)
                                <span class="badge" style="background: #f1f5f9; color: #475569; font-size: 0.78rem; font-weight: 600;">
                                    🔓 Siap Dikerjakan
                                </span>
                            @else
                                <span class="badge badge-neutral" style="font-size: 0.78rem; color: #94a3b8;">
                                    🔒 Belum Dikerjakan
                                </span>
                            @endif
                        </td>
                        <td style="text-align: right;">
                            @if($lvl->exercises_count > 0)
                                <a href="{{ route('student.exercises.show', $lvl) }}" class="btn {{ $isCompleted ? 'btn-secondary' : 'btn-primary' }} btn-sm" style="font-weight: 700; font-size: 0.8rem; padding: 5px 12px;">
                                    {{ $isCompleted ? '🔄 Ulangi Soal' : ($pts > 0 ? '▶ Lanjutkan' : '✍️ Kerjakan') }}
                                </a>
                            @else
                                <span style="font-size: 0.76rem; color: var(--text-muted);">Belum ada soal</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                            Belum ada modul latihan soal yang tersedia untuk mata pelajaran ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
