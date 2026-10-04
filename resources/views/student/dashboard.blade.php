@extends('layouts.app')

@section('title', 'Beranda Siswa - Musashi Learning')

@section('content')
@php
    $studentSubjectId = auth()->user()->subject_id ?? 1;
    $portalLabel = match($studentSubjectId) {
        2 => 'Learning Musashi - Siswa Bahasa Jepang',
        3 => 'Learning Musashi - Siswa Matematika',
        default => 'Learning Musashi - Siswa Bahasa Inggris',
    };
    $portalGradient = match($studentSubjectId) {
        2 => 'linear-gradient(135deg, #831843 0%, #500724 100%)',
        3 => 'linear-gradient(135deg, #064e3b 0%, #022c22 100%)',
        default => 'linear-gradient(135deg, #1e3a8a 0%, #1e1b4b 100%)',
    };
@endphp

<!-- Hero Welcome Banner -->
<div class="hero-banner" style="background: {{ $portalGradient }}; border-radius: var(--radius-xl); padding: 2.25rem 2.5rem; margin-bottom: 2rem; color: #ffffff; box-shadow: var(--shadow-lg);">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.5rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 0.75rem;">
                <span class="badge" style="background: rgba(255,255,255,0.2); color: #ffffff; font-weight: 700; font-size: 0.82rem;">
                    {{ $portalLabel }}
                </span>
                @if(auth()->user()->class_name)
                    <span class="badge" style="background: #22c55e; color: #ffffff; font-weight: 800; font-size: 0.82rem;">
                        🎓 {{ auth()->user()->class_name }}
                    </span>
                @endif
                @if(auth()->user()->division)
                    <span class="badge" style="background: rgba(255,255,255,0.25); color: #ffffff; font-size: 0.82rem;">
                        🏢 {{ auth()->user()->division }}
                    </span>
                @endif
            </div>

            <h1 style="font-size: 2.1rem; margin-bottom: 0.5rem; font-weight: 800; color: #ffffff;">
                Selamat Datang, {{ auth()->user()->name }}! 👋
            </h1>
            <p style="opacity: 0.9; max-width: 640px; font-size: 0.98rem; line-height: 1.6; margin: 0; color: #e2e8f0;">
                Anda terdaftar pada <strong>{{ auth()->user()->class_name ?: 'Kelas Belajar' }}</strong>. Silakan catat presensi kehadiran Anda hari ini dan pelajari seluruh materi presentasi yang tersedia khusus untuk kelas Anda.
            </p>
        </div>

        <div style="background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.2); border-radius: var(--radius-lg); padding: 1.25rem 1.75rem; text-align: center; min-width: 200px;">
            <div style="font-size: 0.78rem; color: #cbd5e1; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Status Akun Siswa</div>
            <div style="font-size: 1.8rem; font-weight: 800; font-family: var(--font-heading); color: #4ade80; margin: 4px 0;">
                AKTIF
            </div>
            <div style="font-size: 0.82rem; color: #e2e8f0;">
                {{ auth()->user()->division ?: 'Musashi Trainee' }}
            </div>
        </div>
    </div>
</div>

<!-- Personal Attendance Statistics (Jumlah Kehadiran, Persentase %, dan Waktu Presensi Realtime) -->
<div class="grid grid-cols-4" style="gap: 1.25rem; margin-bottom: 2rem;">
    <div class="card" style="border-left: 4px solid #10b981; box-shadow: var(--shadow-sm); border-radius: var(--radius-md);">
        <div class="card-body" style="padding: 1.25rem;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 0.78rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Jumlah Kehadiran</div>
                    <div style="font-size: 1.85rem; font-weight: 800; color: #10b981; margin-top: 2px;">
                        {{ $studentAttendanceStats['total_hadir'] }} <span style="font-size: 0.85rem; font-weight: 600; color: #059669;">Hari</span>
                    </div>
                </div>
                <div style="font-size: 2rem; opacity: 0.9;">✅</div>
            </div>
            <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 4px;">Dari total {{ $studentAttendanceStats['total_days'] }} hari tercatat</div>
        </div>
    </div>

    <div class="card" style="border-left: 4px solid #3b82f6; box-shadow: var(--shadow-sm); border-radius: var(--radius-md);">
        <div class="card-body" style="padding: 1.25rem;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 0.78rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Persentase Kehadiran</div>
                    <div style="font-size: 1.85rem; font-weight: 800; color: #2563eb; margin-top: 2px;">
                        {{ $studentAttendanceStats['percentage'] }}%
                    </div>
                </div>
                <div style="font-size: 2rem; opacity: 0.9;">📈</div>
            </div>
            <div style="font-size: 0.78rem; color: #2563eb; font-weight: 600; margin-top: 4px;">Tingkat kedisiplinan presensi</div>
        </div>
    </div>

    <div class="card" style="border-left: 4px solid #f59e0b; box-shadow: var(--shadow-sm); border-radius: var(--radius-md);">
        <div class="card-body" style="padding: 1.25rem;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 0.78rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Total Izin</div>
                    <div style="font-size: 1.85rem; font-weight: 800; color: #d97706; margin-top: 2px;">
                        {{ $studentAttendanceStats['total_izin_ket'] + $studentAttendanceStats['total_izin_tanpa'] }} <span style="font-size: 0.85rem; font-weight: 600; color: #b45309;">Hari</span>
                    </div>
                </div>
                <div style="font-size: 2rem; opacity: 0.9;">📝</div>
            </div>
            <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 4px;">{{ $studentAttendanceStats['total_izin_ket'] }} berizin &bull; {{ $studentAttendanceStats['total_izin_tanpa'] }} tanpa ket.</div>
        </div>
    </div>

    <div class="card" style="border-left: 4px solid #8b5cf6; box-shadow: var(--shadow-sm); border-radius: var(--radius-md);">
        <div class="card-body" style="padding: 1.25rem;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 0.78rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Waktu Presensi Realtime</div>
                    <div style="font-size: 1.22rem; font-weight: 800; color: #4338ca; margin-top: 4px; font-family: monospace;">
                        @if($todayAttendance)
                            {{ $todayAttendance->formatted_time }} <span style="font-size: 0.75rem; font-family: sans-serif; font-weight: 600;">WIB</span>
                        @else
                            <span style="color: #d97706; font-size: 1rem; font-family: sans-serif; font-weight: 700;">Belum Presensi</span>
                        @endif
                    </div>
                </div>
                <div style="font-size: 2rem; opacity: 0.9;">⏱️</div>
            </div>
            <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 4px;">
                @if($todayAttendance)
                    Status: <strong style="color: #065f46;">{{ $todayAttendance->status_label }}</strong>
                @else
                    Silakan isi presensi hari ini di bawah
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Attendance Feature Widget for Today (Presensi Kehadiran Realtime) -->
<div class="card" style="margin-bottom: 2.5rem; border: 2px solid {{ $todayAttendance ? '#10b981' : 'var(--color-primary)' }}; box-shadow: var(--shadow-md); border-radius: var(--radius-lg); overflow: hidden;">
    <div class="card-header" style="background: {{ $todayAttendance ? '#f0fdf4' : '#f8faff' }}; padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 44px; height: 44px; border-radius: var(--radius-md); background: {{ $todayAttendance ? '#10b981' : 'var(--color-primary)' }}; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
                {{ $todayAttendance ? '✅' : '📅' }}
            </div>
            <div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <h2 style="font-size: 1.25rem; margin: 0; color: #0f172a; font-weight: 800;">
                        Presensi Kehadiran Siswa Hari Ini
                    </h2>
                    <span class="badge badge-neutral" style="font-size: 0.8rem; font-family: monospace; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                        <span style="display: inline-block; width: 7px; height: 7px; border-radius: 50%; background: #10b981; box-shadow: 0 0 6px #10b981;"></span>
                        <span id="dashLiveTime">{{ \Carbon\Carbon::now()->format('H:i:s') }}</span> WIB &bull; {{ $todayFormatted }}
                    </span>
                </div>
                <div style="font-size: 0.84rem; color: var(--text-muted); margin-top: 2px;">
                    @if($todayAttendance)
                        Presensi Anda tercatat pada pukul <strong>{{ $todayAttendance->formatted_time }} WIB</strong>
                    @else
                        Silakan pilih status kehadiran Anda hari ini untuk tercatat secara realtime:
                    @endif
                </div>
            </div>
        </div>

        <div>
            @if($todayAttendance)
                <span class="badge {{ $todayAttendance->status_badge_class }}" style="font-size: 0.92rem; padding: 6px 14px; font-weight: 800;">
                    {{ $todayAttendance->status_icon }} {{ $todayAttendance->status_label }}
                </span>
            @else
                <span class="badge badge-warning" style="font-size: 0.85rem; padding: 6px 14px; font-weight: 700;">
                    ⏳ Belum Melakukan Presensi
                </span>
            @endif
        </div>
    </div>

    <div class="card-body" style="padding: 1.5rem;">
        @if($todayAttendance)
            <div style="background: #ffffff; border: 1px solid #d1fae5; border-radius: var(--radius-md); padding: 1.2rem 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem;">
                <div>
                    <span style="font-size: 0.85rem; color: #065f46; font-weight: 700;">Status Terverifikasi:</span>
                    <strong style="color: #064e3b; font-size: 1.1rem; margin-left: 6px;">{{ $todayAttendance->status_label }}</strong>
                    @if($todayAttendance->notes)
                        <div style="margin-top: 6px; font-size: 0.88rem; color: #334155;">
                            Keterangan: <em>"{{ $todayAttendance->notes }}"</em>
                        </div>
                    @endif
                </div>
                <div style="display: flex; gap: 8px;">
                    <a href="{{ route('student.attendance.index') }}" class="btn btn-secondary btn-sm">
                        Lihat Riwayat Lengkap &rarr;
                    </a>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('dashAttendanceForm').style.display = document.getElementById('dashAttendanceForm').style.display === 'none' ? 'block' : 'none'">
                        ✏ Ubah Status
                    </button>
                </div>
            </div>
        @endif

        <div id="dashAttendanceForm" style="{{ $todayAttendance ? 'display: none;' : '' }}">
            <form action="{{ route('attendance.submit') }}" method="POST">
                @csrf
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; margin-bottom: 1.25rem;">
                    <!-- Option 1: Hadir -->
                    <label for="dash_hadir" class="attendance-card-label" id="dash_card_hadir" style="border: 2px solid {{ old('status', $todayAttendance?->status ?? 'hadir') === 'hadir' ? '#10b981' : '#e2e8f0' }}; border-radius: var(--radius-md); padding: 1.15rem; cursor: pointer; background: #ffffff; display: block; transition: all 0.2s;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <input type="radio" name="status" id="dash_hadir" value="hadir" {{ old('status', $todayAttendance?->status ?? 'hadir') === 'hadir' ? 'checked' : '' }} onchange="handleDashAttendanceChange('hadir')" style="accent-color: #10b981; transform: scale(1.2);">
                            <div>
                                <div style="font-weight: 800; font-size: 1.05rem; color: #065f46;">
                                    ✅ Hadir
                                </div>
                                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">Mengikuti sesi belajar bahasa Inggris</div>
                            </div>
                        </div>
                    </label>

                    <!-- Option 2: Izin dengan Keterangan -->
                    <label for="dash_izin_ket" class="attendance-card-label" id="dash_card_izin_ket" style="border: 2px solid {{ old('status', $todayAttendance?->status) === 'izin_keterangan' ? 'var(--color-primary)' : '#e2e8f0' }}; border-radius: var(--radius-md); padding: 1.15rem; cursor: pointer; background: #ffffff; display: block; transition: all 0.2s;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <input type="radio" name="status" id="dash_izin_ket" value="izin_keterangan" {{ old('status', $todayAttendance?->status) === 'izin_keterangan' ? 'checked' : '' }} onchange="handleDashAttendanceChange('izin_keterangan')" style="accent-color: var(--color-primary); transform: scale(1.2);">
                            <div>
                                <div style="font-weight: 800; font-size: 1.05rem; color: var(--color-primary);">
                                    📝 Izin dg Keterangan
                                </div>
                                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">Sakit / urusan mendesak perusahaan</div>
                            </div>
                        </div>
                    </label>

                    <!-- Option 3: Izin tanpa Keterangan -->
                    <label for="dash_izin_tanpa" class="attendance-card-label" id="dash_card_izin_tanpa" style="border: 2px solid {{ old('status', $todayAttendance?->status) === 'izin_tanpa_keterangan' ? '#ef4444' : '#e2e8f0' }}; border-radius: var(--radius-md); padding: 1.15rem; cursor: pointer; background: #ffffff; display: block; transition: all 0.2s;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <input type="radio" name="status" id="dash_izin_tanpa" value="izin_tanpa_keterangan" {{ old('status', $todayAttendance?->status) === 'izin_tanpa_keterangan' ? 'checked' : '' }} onchange="handleDashAttendanceChange('izin_tanpa_keterangan')" style="accent-color: #ef4444; transform: scale(1.2);">
                            <div>
                                <div style="font-weight: 800; font-size: 1.05rem; color: #b91c1c;">
                                    ⚠️ Izin tanpa Keterangan
                                </div>
                                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">Berhalangan tanpa catatan alasan</div>
                            </div>
                        </div>
                    </label>
                </div>

                <!-- Textarea for notes -->
                <div id="dashNotesContainer" style="margin-bottom: 1.25rem; display: {{ old('status', $todayAttendance?->status) === 'izin_keterangan' ? 'block' : 'none' }}; background: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1rem;">
                    <label class="form-label" for="dash_notes" style="font-weight: 700; font-size: 0.88rem; color: #1e1b4b;">
                        Alasan / Keterangan Izin Anda *
                    </label>
                    <textarea name="notes" id="dash_notes" class="form-control" rows="2" placeholder="Tuliskan keterangan izin (contoh: Shift lembur, demam, urusan keluarga)...">{{ old('notes', $todayAttendance?->notes) }}</textarea>
                </div>

                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <button type="submit" class="btn btn-primary" style="font-weight: 800; padding: 10px 24px;">
                        {{ $todayAttendance ? 'Simpan Perubahan Presensi' : 'Kirim Presensi Sekarang' }}
                    </button>
                    @if($todayAttendance)
                        <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('dashAttendanceForm').style.display='none'">
                            Batal
                        </button>
                    @endif
                    <a href="{{ route('student.attendance.index') }}" class="btn btn-secondary btn-sm" style="margin-left: auto;">
                        Buka Riwayat Presensi Lengkap &rarr;
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@if($studentSubjectId == 2)
<!-- Japanese Periodic Test Widget (Khusus Siswa Jepang) -->
<div class="card" style="margin-bottom: 2.5rem; border-left: 5px solid #db2777; box-shadow: var(--shadow-md); border-radius: var(--radius-lg); background: #ffffff;">
    <div class="card-body" style="padding: 1.5rem 1.75rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.25rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                <span class="badge" style="background: #fdf2f8; color: #db2777; font-weight: 800; font-size: 0.8rem;">
                    Ujian Evaluasi Berkala (Per 4 Pertemuan)
                </span>
                <span class="badge badge-neutral" style="font-size: 0.75rem;">12 Pertemuan (Romawi)</span>
            </div>
            <h3 style="font-size: 1.3rem; margin: 0; color: #0f172a; font-weight: 800;">
                Tes Evaluasi Pemahaman Materi & Riwayat Nilai Siswa
            </h3>
            <p style="font-size: 0.88rem; color: var(--text-muted); margin: 4px 0 0; max-width: 580px;">
                Ikuti ujian evaluasi setiap 4 pertemuan materi (Pertemuan 1-4, 5-8, 9-12) dan pantau hasil perolehan nilai tes Anda.
            </p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('student.japanese.tests.index') }}" class="btn btn-primary" style="font-weight: 800; background: #db2777; border-color: #db2777; padding: 10px 20px;">
                📝 Ikuti Tes Evaluasi &rarr;
            </a>
            <a href="{{ route('student.japanese.grades.index') }}" class="btn btn-secondary" style="font-weight: 700; padding: 10px 18px;">
                📊 Riwayat Nilai Saya
            </a>
        </div>
    </div>
</div>
@endif

<!-- Materi Pembelajaran Khusus Kelas Siswa -->
<div style="margin-bottom: 2.5rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.25rem;">
        <div>
            <div class="badge badge-primary" style="font-size: 0.78rem; margin-bottom: 4px;">
                Materi Khusus: {{ auth()->user()->class_name ?: 'Kelas Anda' }}
            </div>
            <h2 style="font-size: 1.45rem; margin: 0; color: #0f172a; display: flex; align-items: center; gap: 8px; font-weight: 800;">
                <span>📚</span> Modul & Slide Materi Belajar Anda
            </h2>
            <p style="font-size: 0.88rem; color: var(--text-muted); margin: 2px 0 0;">
                Berikut adalah modul materi slide presentasi yang telah disiapkan khusus untuk kelas <strong>{{ auth()->user()->class_name ?: 'kelas Anda' }}</strong>.
            </p>
        </div>
        <a href="{{ route('student.materials.index') }}" class="btn btn-secondary btn-sm">
            Lihat Semua Materi ({{ $recentMaterials->count() }}) &rarr;
        </a>
    </div>

    @if($recentMaterials->isNotEmpty())
        <div class="grid grid-cols-3" style="gap: 1.25rem;">
            @foreach($recentMaterials as $mat)
                <div class="card" style="display: flex; flex-direction: column; justify-content: space-between; border-top: 3px solid #2563eb; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
                    <div class="card-body" style="padding: 1.25rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; flex-wrap: wrap; gap: 6px;">
                            <div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">
                                <span class="badge" style="background: #eff6ff; color: #1e40af; font-size: 0.76rem; font-weight: 700;">
                                    🎓 {{ $mat->class_name ?: 'Kelas Umum' }}
                                </span>
                                <span class="badge" style="background: #fdf4ff; color: #a21caf; font-size: 0.76rem; font-weight: 800; border: 1px solid #f0abfc;">
                                    🗓️ {{ $mat->level->name }}
                                </span>
                            </div>
                            <span class="badge badge-neutral" style="font-size: 0.72rem;">
                                {{ strtoupper($mat->file_type ?? 'Modul') }}
                            </span>
                        </div>

                        <div style="display: flex; align-items: flex-start; gap: 10px; margin-bottom: 10px;">
                            <span style="font-size: 1.8rem; line-height: 1;">{{ $mat->file_icon }}</span>
                            <div>
                                <h3 style="font-size: 1.05rem; margin: 0; color: #0f172a; line-height: 1.35; font-weight: 800;">
                                    {{ $mat->title }}
                                </h3>
                                @if($mat->file_name)
                                    <span style="font-size: 0.74rem; color: var(--text-muted); font-family: monospace; display: block; margin-top: 2px;">
                                        📁 {{ Str::limit($mat->file_name, 30) }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        @if($mat->description)
                            <p style="font-size: 0.84rem; color: #475569; line-height: 1.45; margin-bottom: 8px;">
                                {{ Str::limit($mat->description, 90) }}
                            </p>
                        @endif
                    </div>

                    <div class="card-footer" style="background: #f8fafc; padding: 0.75rem 1.25rem; display: flex; gap: 8px; justify-content: space-between;">
                        <a href="{{ route('student.materials.view', $mat) }}" class="btn btn-primary btn-sm" style="flex: 1; text-align: center; font-weight: 700;">
                            Buka Slide Presentasi &rarr;
                        </a>
                        @if($mat->file_path)
                            <a href="{{ route('student.materials.download', $mat) }}" class="btn btn-secondary btn-sm" title="Unduh File Materi">
                                ⬇ Unduh
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="card" style="padding: 2.5rem; text-align: center; color: var(--text-muted); border-radius: var(--radius-lg);">
            <div style="font-size: 2.2rem; margin-bottom: 8px;">📖</div>
            <h3 style="font-size: 1.15rem; color: #0f172a; margin-bottom: 4px;">Materi Belum Diunggah</h3>
            <p style="font-size: 0.88rem; max-width: 480px; margin: 0 auto;">
                Pengajar belum mengunggah materi khusus untuk <strong>{{ auth()->user()->class_name ?: 'kelas ini' }}</strong>. Materi akan muncul otomatis di sini saat pengajar menambahkannya.
            </p>
        </div>
    @endif
</div>

@push('scripts')
<script>
function handleDashAttendanceChange(status) {
    const cardHadir = document.getElementById('dash_card_hadir');
    const cardIzinKet = document.getElementById('dash_card_izin_ket');
    const cardIzinTanpa = document.getElementById('dash_card_izin_tanpa');
    const notesContainer = document.getElementById('dashNotesContainer');

    cardHadir.style.borderColor = (status === 'hadir') ? '#10b981' : '#e2e8f0';
    cardIzinKet.style.borderColor = (status === 'izin_keterangan') ? 'var(--color-primary)' : '#e2e8f0';
    cardIzinTanpa.style.borderColor = (status === 'izin_tanpa_keterangan') ? '#ef4444' : '#e2e8f0';

    if (status === 'izin_keterangan') {
        notesContainer.style.display = 'block';
        document.getElementById('dash_notes').focus();
    } else {
        notesContainer.style.display = 'none';
    }
}

function updateDashClock() {
    const timeEl = document.getElementById('dashLiveTime');
    if (timeEl) {
        const now = new Date();
        const options = { timeZone: 'Asia/Jakarta', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
        timeEl.innerText = new Intl.DateTimeFormat('id-ID', options).format(now).replace(/\./g, ':');
    }
}
setInterval(updateDashClock, 1000);
</script>
@endpush
@endsection
