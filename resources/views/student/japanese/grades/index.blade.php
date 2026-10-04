@extends('layouts.app')

@section('title', 'Riwayat Nilai Tes Evaluasi - Siswa Bahasa Jepang')

@section('content')
<div style="margin-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                <span class="badge" style="background: #fdf2f8; color: #db2777; font-weight: 800;">
                    Kelas {{ auth()->user()->class_name ?: 'Bahasa Jepang' }}
                </span>
                <span class="badge badge-primary">
                    Riwayat Akademik
                </span>
            </div>
            <h1 style="font-size: 1.85rem; margin: 0; font-weight: 800; color: #0f172a;">
                Riwayat Nilai & Evaluasi Tes Siswa
            </h1>
            <p style="color: var(--text-muted); font-size: 0.92rem; margin-top: 4px;">
                Pantau perkembangan perolehan skor nilai tes evaluasi 4 pertemuan Bahasa Jepang Anda di platform Musashi.
            </p>
        </div>
        <a href="{{ route('student.japanese.tests.index') }}" class="btn btn-primary btn-sm" style="font-weight: 700; background: #db2777; border-color: #db2777;">
            📝 Ikuti Tes Evaluasi Baru &rarr;
        </a>
    </div>
</div>

<!-- 3 Statistics Summary Cards -->
<div class="grid grid-cols-3" style="gap: 1.25rem; margin-bottom: 2rem;">
    <div class="card" style="border-left: 4px solid #db2777; box-shadow: var(--shadow-sm); border-radius: var(--radius-md);">
        <div class="card-body" style="padding: 1.25rem;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 0.78rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Total Tes Dikerjakan</div>
                    <div style="font-size: 1.85rem; font-weight: 800; color: #db2777; margin-top: 2px;">
                        {{ $submissions->total() }} <span style="font-size: 0.85rem; font-weight: 600; color: #be185d;">Kali</span>
                    </div>
                </div>
                <div style="font-size: 2rem; opacity: 0.9;">📝</div>
            </div>
            <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 4px;">Ujian berkala per 4 pertemuan</div>
        </div>
    </div>

    <div class="card" style="border-left: 4px solid #10b981; box-shadow: var(--shadow-sm); border-radius: var(--radius-md);">
        <div class="card-body" style="padding: 1.25rem;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 0.78rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Tes Lulus Standar</div>
                    <div style="font-size: 1.85rem; font-weight: 800; color: #10b981; margin-top: 2px;">
                        {{ $passedCount }} <span style="font-size: 0.85rem; font-weight: 600; color: #059669;">Tes</span>
                    </div>
                </div>
                <div style="font-size: 2rem; opacity: 0.9;">✅</div>
            </div>
            <div style="font-size: 0.78rem; color: #059669; font-weight: 600; margin-top: 4px;">Mencapai passing grade (&ge;75)</div>
        </div>
    </div>

    <div class="card" style="border-left: 4px solid #3b82f6; box-shadow: var(--shadow-sm); border-radius: var(--radius-md);">
        <div class="card-body" style="padding: 1.25rem;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 0.78rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Rata-rata Skor</div>
                    <div style="font-size: 1.85rem; font-weight: 800; color: #2563eb; margin-top: 2px;">
                        {{ $averageScore ? round($averageScore, 1) : 0 }} <span style="font-size: 0.85rem; font-weight: 600; color: #1d4ed8;">Poin</span>
                    </div>
                </div>
                <div style="font-size: 2rem; opacity: 0.9;">📈</div>
            </div>
            <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 4px;">Skor performa tes evaluasi</div>
        </div>
    </div>
</div>

<!-- History Table -->
<div class="card" style="box-shadow: var(--shadow-sm); border-radius: var(--radius-lg); overflow: hidden;">
    <div class="card-header" style="background: #ffffff; padding: 1.25rem 1.5rem;">
        <h3 style="font-size: 1.2rem; margin: 0; font-weight: 800; color: #0f172a;">
            Daftar Catatan Riwayat Nilai Tes Siswa
        </h3>
    </div>

    <div class="table-responsive">
        <table class="table" style="vertical-align: middle;">
            <thead>
                <tr style="background: #f8fafc; font-size: 0.84rem;">
                    <th style="width: 50px; text-align: center;">No</th>
                    <th>Judul Tes Evaluasi</th>
                    <th>Materi Cakupan</th>
                    <th style="text-align: center;">Benar / Soal</th>
                    <th style="text-align: center;">Skor Nilai</th>
                    <th style="text-align: center;">Status</th>
                    <th>Waktu Pengerjaan</th>
                    <th>Feedback Guru</th>
                </tr>
            </thead>
            <tbody>
                @forelse($submissions as $idx => $sub)
                    <tr>
                        <td style="text-align: center; font-weight: 700; color: #64748b;">
                            {{ $submissions->firstItem() + $idx }}
                        </td>
                        <td>
                            <strong style="color: #0f172a; font-size: 0.95rem;">{{ $sub->test?->title ?? 'Tes Evaluasi' }}</strong>
                        </td>
                        <td>
                            <span class="badge" style="background: #fdf2f8; color: #be185d; font-weight: 800; font-size: 0.78rem;">
                                🗓️ Pertemuan {{ $sub->test?->start_meeting ?? 1 }} - {{ $sub->test?->end_meeting ?? 4 }}
                            </span>
                        </td>
                        <td style="text-align: center; font-weight: 700;">
                            {{ $sub->correct_count }} / {{ $sub->total_questions }}
                        </td>
                        <td style="text-align: center;">
                            <span style="font-size: 1.35rem; font-weight: 900; color: {{ $sub->score >= 75 ? '#10b981' : '#ef4444' }}; font-family: monospace;">
                                {{ $sub->score }}
                            </span>
                        </td>
                        <td style="text-align: center;">
                            @if($sub->is_passed)
                                <span class="badge badge-success" style="font-size: 0.82rem; font-weight: 700;">
                                    ✅ Lulus
                                </span>
                            @else
                                <span class="badge badge-danger" style="font-size: 0.82rem; font-weight: 700;">
                                    ⚠️ Perlu Remidial
                                </span>
                            @endif
                        </td>
                        <td style="font-size: 0.82rem; color: #64748b;">
                            {{ $sub->created_at->format('d M Y, H:i') }} WIB
                        </td>
                        <td style="font-size: 0.82rem; color: #475569; max-width: 220px;">
                            {{ $sub->teacher_feedback ?? '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                            <div style="font-size: 2.2rem; margin-bottom: 8px;">📊</div>
                            <h4 style="color: #0f172a; margin-bottom: 4px;">Belum Ada Riwayat Nilai Tes</h4>
                            <p style="font-size: 0.88rem; max-width: 440px; margin: 0 auto 1.25rem;">
                                Anda belum mengerjakan tes evaluasi 4 pertemuan. Nilai tes yang Anda kerjakan akan muncul di sini.
                            </p>
                            <a href="{{ route('student.japanese.tests.index') }}" class="btn btn-primary btn-sm" style="font-weight: 700; background: #db2777; border-color: #db2777;">
                                Mulai Tes Evaluasi Sekarang &rarr;
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($submissions->hasPages())
        <div class="card-footer" style="background: #ffffff; padding: 1rem 1.5rem;">
            {{ $submissions->links() }}
        </div>
    @endif
</div>
@endsection
