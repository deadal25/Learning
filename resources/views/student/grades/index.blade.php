@extends('layouts.app')

@section('title', 'Riwayat Nilai & Evaluasi - Siswa Musashi')

@section('content')
@php
    $evaluatedMonths = $monthlyGrades->filter(fn($g) => $g->final_score > 0 || !empty($g->feedback) || (float)$g->attendance_score > 0 || (float)$g->total_exam > 0);
    $avgFinalScore = $monthlyGrades->where('final_score', '>', 0)->avg('final_score');
@endphp

<div style="margin-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div class="badge badge-primary" style="margin-bottom: 6px;">
                🎓 Kelas: {{ $user->class_name ?: 'Bahasa Inggris' }} &bull; Divisi: {{ $user->division ?: 'Umum' }}
            </div>
            <h1 style="font-size: 1.85rem; margin: 0; font-weight: 800; color: #0f172a;">
                📊 Rekap Nilai & Riwayat Belajar
            </h1>
            <p style="color: var(--text-muted); font-size: 0.92rem; margin-top: 4px;">
                Pantau lembar evaluasi nilai bulanan dari guru pengajar serta perolehan skor latihan soal {{ $subject->name ?? 'Bahasa Inggris' }}.
            </p>
        </div>
        <a href="{{ route('student.dashboard') }}" class="btn btn-secondary btn-sm">
            &larr; Kembali ke Beranda
        </a>
    </div>
</div>

<!-- Stat Cards -->
<div class="grid grid-cols-4" style="gap: 1.25rem; margin-bottom: 2rem;">
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
            <div style="font-size: 0.76rem; font-weight: 700; color: #b45309; text-transform: uppercase; letter-spacing: 0.5px;">Rata-Rata Skor Latihan</div>
            <div style="font-size: 1.85rem; font-weight: 800; color: #d97706; margin-top: 4px;">
                {{ $averageScore }} <span style="font-size: 0.85rem; font-weight: 600; color: #b45309;">/ 100 Poin</span>
            </div>
            <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 2px;">Dari {{ $attemptedCount }} level yang dicoba</div>
        </div>
    </div>

    <div class="card" style="border-left: 4px solid #8b5cf6; box-shadow: var(--shadow-sm); border-radius: var(--radius-md);">
        <div class="card-body" style="padding: 1.25rem;">
            <div style="font-size: 0.76rem; font-weight: 700; color: #6d28d9; text-transform: uppercase; letter-spacing: 0.5px;">Evaluasi Guru (Nilai Akhir)</div>
            <div style="font-size: 1.85rem; font-weight: 800; color: #7c3aed; margin-top: 4px;">
                @if($avgFinalScore)
                    {{ round($avgFinalScore, 1) }} <span style="font-size: 0.85rem; font-weight: 600; color: #6d28d9;">/ 100</span>
                @else
                    <span style="font-size: 1.2rem; color: #94a3b8; font-weight: 700;">Belum Ada</span>
                @endif
            </div>
            <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 2px;">{{ $evaluatedMonths->count() }} bulan telah dievaluasi</div>
        </div>
    </div>
</div>

<!-- Section 1: Lembar Nilai & Feedback Bulanan dari Guru -->
<div class="card" style="box-shadow: var(--shadow-sm); border-radius: var(--radius-lg); overflow: hidden; border: 1px solid #e2e8f0; margin-bottom: 2.5rem;">
    <div class="card-header" style="background: #ffffff; padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; flex-wrap: wrap; gap: 10px;">
        <div>
            <h3 style="font-size: 1.15rem; margin: 0; font-weight: 800; color: #0f172a;">
                📋 Lembar Nilai & Feedback Bulanan dari Guru (Bulan 1 s/d 5)
            </h3>
            <p style="font-size: 0.82rem; color: var(--text-muted); margin: 2px 0 0;">
                Penilaian resmi mencakup nilai kehadiran, skor ujian kompetensi bahasa (Speaking & Grammar), serta catatan feedback.
            </p>
        </div>
        <span class="badge" style="background: #f5f3ff; color: #6d28d9; font-weight: 700; border: 1px solid #ddd6fe;">
            Guru Pengajar: {{ $monthlyGrades->first()?->teacher?->name ?? 'Belum Ditentukan' }}
        </span>
    </div>

    @if($monthlyGrades->isNotEmpty())
        <div class="table-responsive">
            <table class="table" style="vertical-align: middle; margin-bottom: 0;">
                <thead>
                    <tr style="background: #f8fafc; font-weight: 800; font-size: 0.82rem; color: #475569;">
                        <th style="width: 110px; text-align: center;">Periode</th>
                        <th style="min-width: 170px; text-align: center;">Kehadiran (Pertemuan 1-4)</th>
                        <th style="min-width: 230px; text-align: center;">Ujian Bahasa (Fluency, Grammar, Pronunc, Vocab)</th>
                        <th style="width: 140px; text-align: center;">Nilai Akhir</th>
                        <th style="min-width: 260px;">Catatan & Feedback Guru</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($monthlyGrades as $grade)
                        @php
                            $fs = (float) $grade->final_score;
                            $hasEvaluated = $fs > 0 || !empty($grade->feedback) || (float)$grade->attendance_score > 0 || (float)$grade->total_exam > 0;
                        @endphp
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="text-align: center; vertical-align: middle;">
                                <div style="font-weight: 800; font-size: 1rem; color: #1e293b;">
                                    Bulan ke-{{ $grade->week }}
                                </div>
                                <span class="badge" style="background: #eff6ff; color: #2563eb; font-size: 0.72rem; margin-top: 2px;">
                                    {{ $grade->class_name }}
                                </span>
                            </td>
                            <td style="text-align: center; vertical-align: middle;">
                                <div style="display: flex; justify-content: center; gap: 4px; margin-bottom: 4px; font-size: 0.76rem;">
                                    <span style="background: #f1f5f9; padding: 2px 6px; border-radius: 4px; color: #475569;" title="Pertemuan 1">M1: <strong>{{ (float)$grade->meeting_1 }}</strong></span>
                                    <span style="background: #f1f5f9; padding: 2px 6px; border-radius: 4px; color: #475569;" title="Pertemuan 2">M2: <strong>{{ (float)$grade->meeting_2 }}</strong></span>
                                    <span style="background: #f1f5f9; padding: 2px 6px; border-radius: 4px; color: #475569;" title="Pertemuan 3">M3: <strong>{{ (float)$grade->meeting_3 }}</strong></span>
                                    <span style="background: #f1f5f9; padding: 2px 6px; border-radius: 4px; color: #475569;" title="Pertemuan 4">M4: <strong>{{ (float)$grade->meeting_4 }}</strong></span>
                                </div>
                                <div style="font-size: 0.84rem; font-weight: 700; color: #0284c7;">
                                    Attendance: {{ (float)$grade->attendance_score }}
                                </div>
                            </td>
                            <td style="text-align: center; vertical-align: middle;">
                                <div style="display: flex; justify-content: center; gap: 4px; margin-bottom: 4px; font-size: 0.75rem;">
                                    <span style="background: #fef3c7; color: #92400e; padding: 2px 6px; border-radius: 4px;" title="Fluency">Flu: <strong>{{ (float)$grade->fluency }}</strong></span>
                                    <span style="background: #fef3c7; color: #92400e; padding: 2px 6px; border-radius: 4px;" title="Grammar">Gra: <strong>{{ (float)$grade->grammar }}</strong></span>
                                    <span style="background: #fef3c7; color: #92400e; padding: 2px 6px; border-radius: 4px;" title="Pronunciation">Pro: <strong>{{ (float)$grade->pronunciation }}</strong></span>
                                    <span style="background: #fef3c7; color: #92400e; padding: 2px 6px; border-radius: 4px;" title="Vocabulary">Voc: <strong>{{ (float)$grade->vocabulary }}</strong></span>
                                </div>
                                <div style="font-size: 0.84rem; font-weight: 700; color: #b45309;">
                                    Total Exam: {{ (float)$grade->total_exam }}
                                </div>
                            </td>
                            <td style="text-align: center; vertical-align: middle;">
                                @if($fs >= 85)
                                    <div style="font-size: 1.25rem; font-weight: 800; color: #15803d;">{{ $fs }}</div>
                                    <span class="badge" style="background: #dcfce7; color: #166534; font-size: 0.72rem; font-weight: 800;">🌟 Sangat Baik</span>
                                @elseif($fs >= 75)
                                    <div style="font-size: 1.25rem; font-weight: 800; color: #1d4ed8;">{{ $fs }}</div>
                                    <span class="badge" style="background: #dbeafe; color: #1e40af; font-size: 0.72rem; font-weight: 800;">✅ Baik</span>
                                @elseif($fs >= 60)
                                    <div style="font-size: 1.25rem; font-weight: 800; color: #b45309;">{{ $fs }}</div>
                                    <span class="badge" style="background: #fef3c7; color: #92400e; font-size: 0.72rem; font-weight: 800;">⚠️ Cukup</span>
                                @elseif($fs > 0)
                                    <div style="font-size: 1.25rem; font-weight: 800; color: #dc2626;">{{ $fs }}</div>
                                    <span class="badge" style="background: #fee2e2; color: #991b1b; font-size: 0.72rem; font-weight: 800;">Perlu Bimbingan</span>
                                @else
                                    <div style="font-size: 1.1rem; font-weight: 700; color: #94a3b8;">-</div>
                                    <span class="badge badge-neutral" style="font-size: 0.72rem;">Belum Dinilai</span>
                                @endif
                            </td>
                            <td style="vertical-align: middle;">
                                @if(!empty($grade->feedback))
                                    <div style="background: #f8fafc; border-left: 3px solid #2563eb; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem; color: #334155; line-height: 1.4;">
                                        <div style="font-size: 0.72rem; font-weight: 700; color: #2563eb; margin-bottom: 2px;">💬 Catatan Guru:</div>
                                        "{{ $grade->feedback }}"
                                    </div>
                                @else
                                    <span style="font-size: 0.8rem; color: #94a3b8; font-style: italic;">
                                        Belum ada catatan feedback khusus.
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div style="padding: 2.5rem 1.5rem; text-align: center; color: var(--text-muted);">
            <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">📝</div>
            <h4 style="font-weight: 700; color: #334155; margin-bottom: 4px;">Belum Ada Rekap Nilai Bulanan dari Guru</h4>
            <p style="font-size: 0.88rem; max-width: 500px; margin: 0 auto; line-height: 1.5;">
                Guru pengajar belum mempublikasikan lembar penilaian untuk periode ini. Nilai kehadiran, hasil ujian, dan feedback akan otomatis tampil di sini setelah diisi oleh guru.
            </p>
        </div>
    @endif
</div>

<!-- Section 2: Main Table: Exercise Score History -->
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
