@extends('layouts.app')

@section('title', 'Latihan Soal & Evaluasi Bahasa Jepang - Siswa Musashi')

@section('content')
<div style="margin-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                <span class="badge" style="background: #fdf2f8; color: #db2777; font-weight: 800;">
                    🇯🇵 Kelas {{ auth()->user()->class_name ?: 'Bahasa Jepang' }}
                </span>
                <span class="badge badge-primary">
                    12 Pertemuan Belajar
                </span>
                <span class="badge" style="background: #ecfdf5; color: #047857; font-weight: 700;">
                    🔀 Soal Acak Otomatis
                </span>
            </div>
            <h1 style="font-size: 1.85rem; margin: 0; font-weight: 800; color: #0f172a;">
                Latihan Soal & Ujian Evaluasi Bahasa Jepang
            </h1>
            <p style="color: var(--text-muted); font-size: 0.92rem; margin-top: 4px;">
                Kerjakan latihan soal per pertemuan (10 nomor acak) dan ujian evaluasi per 4 pertemuan (15 nomor acak). 
                <span style="color: #db2777; font-weight: 600;">Soal hanya dapat diakses apabila sudah diaktifkan oleh Guru Bahasa Jepang.</span>
            </p>
        </div>
        <a href="{{ route('student.japanese.grades.index') }}" class="btn btn-secondary" style="font-weight: 700;">
            📊 Riwayat Nilai & Hasil Tes Saya &rarr;
        </a>
    </div>
</div>

@if($tests->isEmpty())
    <div class="card" style="box-shadow: var(--shadow-sm); border-radius: var(--radius-lg); text-align: center; padding: 3.5rem 2rem; background: #ffffff;">
        <div style="width: 72px; height: 72px; margin: 0 auto 1.25rem; background: #fdf2f8; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.25rem;">
            ⏳
        </div>
        <h3 style="font-size: 1.35rem; font-weight: 800; color: #0f172a; margin: 0 0 0.5rem;">
            Belum Ada Tes Evaluasi yang Diaktifkan
        </h3>
        <p style="color: var(--text-muted); font-size: 0.95rem; max-width: 540px; margin: 0 auto 1.75rem; line-height: 1.5;">
            Sensei belum mengaktifkan latihan soal maupun ujian evaluasi berkala untuk kelas Anda. Latihan soal per pertemuan (10 soal acak) dan evaluasi per 4 pertemuan (15 soal acak) akan dapat diakses setelah guru mengaktifkannya.
        </p>
        <div style="display: flex; justify-content: center; gap: 12px; flex-wrap: wrap;">
            <a href="{{ route('student.materials.index') }}" class="btn btn-primary" style="font-weight: 700; background: #db2777; border-color: #db2777;">
                📚 Buka Materi Pembelajaran
            </a>
            <a href="{{ route('student.attendance.index') }}" class="btn btn-secondary" style="font-weight: 700;">
                🗓️ Cek Presensi & Kehadiran
            </a>
        </div>
    </div>
@else
    <!-- Tabs Navigation -->
    <div style="display: flex; gap: 10px; margin-bottom: 1.75rem; border-bottom: 2px solid #e2e8f0; padding-bottom: 4px;">
        <button type="button" id="tabMeetingBtn" onclick="switchStudentTab('meeting')" style="padding: 10px 20px; font-weight: 800; font-size: 0.95rem; border: none; background: none; border-bottom: 3px solid #db2777; color: #db2777; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s;">
            <span>📝 Latihan Soal Per Pertemuan</span>
            <span class="badge" style="background: #fdf2f8; color: #be185d; font-size: 0.75rem;">{{ $meetingTests->count() }} Aktif (10 Soal Acak)</span>
        </button>
        <button type="button" id="tabPeriodicBtn" onclick="switchStudentTab('periodic')" style="padding: 10px 20px; font-weight: 800; font-size: 0.95rem; border: none; background: none; border-bottom: 3px solid transparent; color: #64748b; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s;">
            <span>🏆 Ujian Evaluasi Per 4 Pertemuan</span>
            <span class="badge badge-neutral" style="font-size: 0.75rem;">{{ $periodicTests->count() }} Aktif (15 Soal Acak)</span>
        </button>
    </div>

    <!-- Tab 1: Latihan Soal Per Pertemuan (10 Soal Tiap Pertemuan) -->
    <div id="tabMeetingContent">
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: var(--radius-lg); padding: 1rem 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 1.5rem;">📝</span>
                <div>
                    <strong style="color: #0f172a; font-size: 0.95rem;">Latihan Soal Mandiri Per Pertemuan (10 Soal Acak)</strong>
                    <div style="font-size: 0.82rem; color: #64748b;">Setiap latihan terdiri dari 10 butir soal yang diacak secara otomatis saat mulai dikerjakan.</div>
                </div>
            </div>
            <span class="badge" style="background: #ffffff; border: 1px solid #cbd5e1; color: #334155; font-weight: 700;">
                {{ $meetingTests->count() }} Pertemuan Aktif
            </span>
        </div>

        <div class="grid grid-cols-3" style="gap: 1.25rem;">
            @forelse($meetingTests as $test)
                @php
                    $sub = $userSubmissions->get($test->id);
                @endphp
                <div class="card" style="box-shadow: var(--shadow-sm); border-radius: var(--radius-lg); border-top: 4px solid {{ $sub ? ($sub->is_passed ? '#10b981' : '#f59e0b') : '#db2777' }}; display: flex; flex-direction: column; justify-content: space-between; background: #ffffff;">
                    <div class="card-body" style="padding: 1.25rem 1.5rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <span class="badge" style="background: #fdf2f8; color: #be185d; font-weight: 800; font-size: 0.78rem;">
                                🗓️ Pertemuan {{ $test->start_meeting }}
                            </span>

                            <span class="badge" style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; font-weight: 800; font-size: 0.72rem;">
                                🟢 Diaktifkan Guru
                            </span>
                        </div>

                        <h3 style="font-size: 1.05rem; margin: 0 0 6px; color: #0f172a; font-weight: 800; line-height: 1.35;">
                            {{ $test->title }}
                        </h3>

                        <p style="font-size: 0.83rem; color: var(--text-muted); line-height: 1.4; margin-bottom: 1rem; min-height: 2.8em;">
                            {{ $test->description }}
                        </p>

                        <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 10px 12px; margin-bottom: 0.75rem;">
                            <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 5px;">
                                <span style="color: var(--text-muted);">Sistem Soal:</span>
                                <strong style="color: #db2777;">{{ $test->target_questions ?: 10 }} Soal Acak</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 5px;">
                                <span style="color: var(--text-muted);">Durasi & KKM:</span>
                                <span>⏱️ {{ $test->duration_minutes }}m | KKM {{ $test->pass_score }}%</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 0.8rem;">
                                <span style="color: var(--text-muted);">Status Nilai:</span>
                                @if($sub)
                                    <strong style="color: {{ $sub->is_passed ? '#059669' : '#d97706' }};">
                                        {{ $sub->is_passed ? '✅ Lulus (' . $sub->score . ')' : '⚠️ Skor: ' . $sub->score }}
                                    </strong>
                                @else
                                    <span style="color: #64748b; font-weight: 600;">Belum Mengerjakan</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="card-footer" style="background: #ffffff; padding: 0.85rem 1.25rem; border-top: 1px solid var(--border-color);">
                        @if($sub)
                            <div style="display: flex; gap: 6px;">
                                <a href="{{ route('student.japanese.tests.show', $test) }}" class="btn btn-secondary btn-sm" style="flex: 1; text-align: center; font-weight: 700; font-size: 0.82rem;">
                                    🔄 Ulangi (10 Soal Acak)
                                </a>
                                <a href="{{ route('student.japanese.grades.index') }}" class="btn btn-primary btn-sm" style="font-weight: 700; font-size: 0.82rem;">
                                    Nilai
                                </a>
                            </div>
                        @else
                            <a href="{{ route('student.japanese.tests.show', $test) }}" class="btn btn-primary btn-sm" style="display: block; text-align: center; font-weight: 800; background: #db2777; border-color: #db2777; padding: 8px;">
                                Kerjakan Latihan (10 Soal Acak) &rarr;
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 2.5rem; background: #f8fafc; border-radius: var(--radius-md); border: 1px dashed #cbd5e1; color: #64748b;">
                    Belum ada latihan per pertemuan yang diaktifkan oleh Guru Bahasa Jepang.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Tab 2: Ujian Evaluasi Per 4 Pertemuan (15 Soal) -->
    <div id="tabPeriodicContent" style="display: none;">
        <div style="background: #fdf2f8; border: 1px solid #fbcfe8; border-radius: var(--radius-lg); padding: 1rem 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 1.5rem;">🏆</span>
                <div>
                    <strong style="color: #831843; font-size: 0.95rem;">Ujian Evaluasi Berkala Per 4 Pertemuan (15 Nomor Acak)</strong>
                    <div style="font-size: 0.82rem; color: #9d174d;">Evaluasi komprehensif menguji gabungan materi 4 pertemuan sekaligus untuk mengukur capaian belajar.</div>
                </div>
            </div>
            <span class="badge" style="background: #ffffff; border: 1px solid #f472b6; color: #be185d; font-weight: 800;">
                15 Soal Acak per Evaluasi
            </span>
        </div>

        <div class="grid grid-cols-3" style="gap: 1.5rem;">
            @forelse($periodicTests as $test)
                @php
                    $sub = $userSubmissions->get($test->id);
                @endphp
                <div class="card" style="box-shadow: var(--shadow-sm); border-radius: var(--radius-lg); border-top: 4px solid {{ $sub ? ($sub->is_passed ? '#10b981' : '#f59e0b') : '#db2777' }}; display: flex; flex-direction: column; justify-content: space-between; background: #ffffff;">
                    <div class="card-body" style="padding: 1.5rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                            <span class="badge" style="background: #fdf2f8; color: #be185d; font-weight: 800; font-size: 0.78rem;">
                                🗓️ Pertemuan {{ $test->start_meeting }} - {{ $test->end_meeting }}
                            </span>

                            <span class="badge" style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; font-weight: 800; font-size: 0.72rem;">
                                🟢 Diaktifkan Guru
                            </span>
                        </div>

                        <h3 style="font-size: 1.15rem; margin: 0 0 8px; color: #0f172a; font-weight: 800; line-height: 1.35;">
                            {{ $test->title }}
                        </h3>

                        <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.45; margin-bottom: 1.25rem;">
                            {{ $test->description }}
                        </p>

                        <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 12px 14px; margin-bottom: 1rem;">
                            <div style="display: flex; justify-content: space-between; font-size: 0.82rem; margin-bottom: 6px;">
                                <span style="color: var(--text-muted);">Jumlah Soal:</span>
                                <strong style="color: #db2777;">{{ $test->target_questions ?: 15 }} Soal Acak</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 0.82rem; margin-bottom: 6px;">
                                <span style="color: var(--text-muted);">Durasi & KKM:</span>
                                <strong style="color: #2563eb;">⏱️ {{ $test->duration_minutes }}m | KKM {{ $test->pass_score }}%</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 0.82rem;">
                                <span style="color: var(--text-muted);">Status Pengerjaan:</span>
                                @if($sub)
                                    <strong style="color: {{ $sub->is_passed ? '#059669' : '#d97706' }};">
                                        {{ $sub->is_passed ? '✅ Lulus (' . $sub->score . ')' : '⚠️ Skor: ' . $sub->score }}
                                    </strong>
                                @else
                                    <span style="color: #64748b; font-weight: 600;">Belum Mengerjakan</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="card-footer" style="background: #ffffff; padding: 1rem 1.5rem; border-top: 1px solid var(--border-color);">
                        @if($sub)
                            <div style="display: flex; gap: 8px;">
                                <a href="{{ route('student.japanese.tests.show', $test) }}" class="btn btn-secondary btn-sm" style="flex: 1; text-align: center; font-weight: 700;">
                                    🔄 Ulangi Evaluasi (15 Soal Acak)
                                </a>
                                <a href="{{ route('student.japanese.grades.index') }}" class="btn btn-primary btn-sm" style="font-weight: 700;">
                                    Skor
                                </a>
                            </div>
                        @else
                            <a href="{{ route('student.japanese.tests.show', $test) }}" class="btn btn-primary btn-sm" style="display: block; text-align: center; font-weight: 800; background: #db2777; border-color: #db2777; padding: 10px;">
                                Mulai Tes Evaluasi (15 Soal Acak) &rarr;
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 2.5rem; background: #fdf2f8; border-radius: var(--radius-md); border: 1px dashed #fbcfe8; color: #831843;">
                    Belum ada ujian evaluasi berkala yang diaktifkan oleh Guru Bahasa Jepang.
                </div>
            @endforelse
        </div>
    </div>
@endif

@push('scripts')
<script>
function switchStudentTab(tab) {
    const meetingContent = document.getElementById('tabMeetingContent');
    const periodicContent = document.getElementById('tabPeriodicContent');
    const tabMeetingBtn = document.getElementById('tabMeetingBtn');
    const tabPeriodicBtn = document.getElementById('tabPeriodicBtn');

    if (!meetingContent || !periodicContent) return;

    if (tab === 'meeting') {
        meetingContent.style.display = 'block';
        periodicContent.style.display = 'none';

        tabMeetingBtn.style.borderBottomColor = '#db2777';
        tabMeetingBtn.style.color = '#db2777';

        tabPeriodicBtn.style.borderBottomColor = 'transparent';
        tabPeriodicBtn.style.color = '#64748b';
    } else {
        meetingContent.style.display = 'none';
        periodicContent.style.display = 'block';

        tabMeetingBtn.style.borderBottomColor = 'transparent';
        tabMeetingBtn.style.color = '#64748b';

        tabPeriodicBtn.style.borderBottomColor = '#db2777';
        tabPeriodicBtn.style.color = '#db2777';
    }
}
</script>
@endpush
@endsection
