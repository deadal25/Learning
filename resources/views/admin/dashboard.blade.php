@extends('layouts.app')

@php
    $subjectName = 'Bahasa Inggris';
    $teacherTitle = 'Learning Musashi Guru Bahasa Inggris';
    if(auth()->user()->subject_id == 2) {
        $subjectName = 'Bahasa Jepang';
        $teacherTitle = 'Learning Musashi Guru Bahasa Jepang';
    } elseif(auth()->user()->subject_id == 3) {
        $subjectName = 'Matematika';
        $teacherTitle = 'Learning Musashi Guru Matematika';
    }
@endphp

@section('title', $teacherTitle)

@section('content')
<!-- Hero Banner -->
<div class="hero-banner" style="background: linear-gradient(135deg, {{ auth()->user()->subject_id == 2 ? '#831843 0%, #be185d 50%, #db2777 100%' : (auth()->user()->subject_id == 3 ? '#064e3b 0%, #059669 50%, #10b981 100%' : '#1e3a8a 0%, #2563eb 50%, #4f46e5 100%') }});">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.25rem;">
        <div>
            <div class="badge" style="background: rgba(255,255,255,0.2); color: #fff; margin-bottom: 0.75rem; font-weight: 700;">
                @if(auth()->user()->subject_id == 2)
                    Learning Musashi Guru Bahasa Jepang
                @elseif(auth()->user()->subject_id == 3)
                    Learning Musashi Guru Matematika
                @else
                    Learning Musashi Guru Bahasa Inggris
                @endif
            </div>
            <h1 style="font-size: 2rem; margin-bottom: 0.5rem; color: #fff;">Halo, {{ auth()->user()->name }}! 👋</h1>
            @if(auth()->user()->subject_id == 2)
                <p style="opacity: 0.92; max-width: 650px; font-size: 0.95rem; color: #f1f5f9; line-height: 1.5;">
                    Selamat datang di Learning Musashi Bahasa Jepang. Anda memiliki kendali penuh untuk mengelola materi per grup, memantau absensi realtime tiap grup, serta mengelola latihan soal dan ujian evaluasi berkala.
                </p>
            @elseif(auth()->user()->subject_id == 3)
                <p style="opacity: 0.92; max-width: 650px; font-size: 0.95rem; color: #f1f5f9; line-height: 1.5;">
                    Selamat datang di Learning Musashi Matematika. Anda memiliki kendali penuh untuk mengelola modul materi per kelas, memantau absensi realtime siswa, serta mengelola bank butir soal latihan matematika.
                </p>
            @else
                <p style="opacity: 0.92; max-width: 650px; font-size: 0.95rem; color: #f1f5f9; line-height: 1.5;">
                    Selamat datang di Learning Musashi Bahasa Inggris. Anda memiliki kendali penuh untuk mengelola materi per kelas, memantau absensi realtime tiap kelas, serta menginput nilai dan evaluasi mingguan siswa.
                </p>
            @endif
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            @if(auth()->user()->subject_id == 2)
                <a href="{{ route('admin.japanese.tests.index') }}" class="btn btn-warning" style="font-weight: 700; box-shadow: 0 4px 12px rgba(219, 39, 119, 0.4); background: #fdf2f8; color: #be185d; border-color: #fbcfe8;">
                    📝 Kelola Tes Siswa
                </a>
            @elseif(auth()->user()->subject_id == 3)
                <a href="{{ route('admin.exercises.index') }}" class="btn btn-warning" style="font-weight: 700; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4); background: #ecfdf5; color: #047857; border-color: #a7f3d0;">
                    📊 Kelola Bank Soal
                </a>
            @else
                <a href="{{ route('admin.grades.index') }}" class="btn btn-warning" style="font-weight: 700; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);">
                    ⭐ Input Nilai Siswa
                </a>
            @endif
            <a href="{{ route('admin.materials.create') }}" class="btn btn-secondary" style="background: rgba(255,255,255,0.2); color: #fff; border-color: rgba(255,255,255,0.35);">
                + Upload Materi Kelas
            </a>
        </div>
    </div>
</div>

<!-- Teacher's Daily Check-in & Today Attendance Overview -->
<div class="grid grid-cols-2" style="margin-bottom: 2rem;">
    <!-- Teacher Daily Attendance Card -->
    <div class="card" style="border-left: 4px solid var(--color-success); box-shadow: var(--shadow-sm);">
        <div class="card-body" style="padding: 1.5rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem; flex-wrap: wrap; gap: 8px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 1.6rem;">🗓️</span>
                    <div>
                        <strong style="font-size: 1.05rem; color: #0f172a; display: block;">Presensi Kehadiran Guru Hari Ini</strong>
                        <div style="font-size: 0.78rem; font-family: monospace; color: #475569; display: flex; align-items: center; gap: 5px; margin-top: 2px;">
                            <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #10b981; box-shadow: 0 0 5px #10b981;"></span>
                            <span id="teacherDashClock">{{ \Carbon\Carbon::now()->format('H:i:s') }}</span> WIB &bull; {{ \Carbon\Carbon::now()->translatedFormat('d M Y') }}
                        </div>
                    </div>
                </div>
                @if($teacherTodayAttendance)
                    @if($teacherTodayAttendance->status === 'hadir')
                        <span class="badge badge-success">✅ Hadir Mengajar</span>
                    @elseif($teacherTodayAttendance->status === 'izin_keterangan')
                        <span class="badge badge-primary">📝 Izin dg Keterangan</span>
                    @else
                        <span class="badge badge-danger">⚠️ Izin tanpa Ket.</span>
                    @endif
                @else
                    <span class="badge badge-warning">⏳ Belum Presensi</span>
                @endif
            </div>

            @if($teacherTodayAttendance)
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: var(--radius-md); padding: 0.85rem; font-size: 0.88rem; margin-bottom: 0.75rem;">
                    <div>Status Anda: <strong>{{ $teacherTodayAttendance->status_label }}</strong></div>
                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">
                        Pukul: {{ $teacherTodayAttendance->check_in_time ?? '-' }} WIB
                        @if($teacherTodayAttendance->notes)
                            &bull; Catatan: <em>"{{ $teacherTodayAttendance->notes }}"</em>
                        @endif
                    </div>
                </div>
            @endif

            <form action="{{ route('attendance.submit') }}" method="POST">
                @csrf
                <div style="margin-bottom: 0.6rem;">
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <label style="display: flex; align-items: center; gap: 4px; font-size: 0.82rem; cursor: pointer; background: #ecfdf5; padding: 5px 10px; border-radius: 6px; border: 1px solid #a7f3d0;">
                            <input type="radio" name="status" value="hadir" {{ (!$teacherTodayAttendance || $teacherTodayAttendance->status === 'hadir') ? 'checked' : '' }} onchange="toggleTeacherNotes(false)">
                            <strong style="color: #065f46;">Hadir Mengajar</strong>
                        </label>
                        <label style="display: flex; align-items: center; gap: 4px; font-size: 0.82rem; cursor: pointer; background: #eff6ff; padding: 5px 10px; border-radius: 6px; border: 1px solid #bfdbfe;">
                            <input type="radio" name="status" value="izin_keterangan" {{ ($teacherTodayAttendance && $teacherTodayAttendance->status === 'izin_keterangan') ? 'checked' : '' }} onchange="toggleTeacherNotes(true)">
                            <strong style="color: #1e40af;">Izin dg Keterangan</strong>
                        </label>
                        <label style="display: flex; align-items: center; gap: 4px; font-size: 0.82rem; cursor: pointer; background: #fef2f2; padding: 5px 10px; border-radius: 6px; border: 1px solid #fecaca;">
                            <input type="radio" name="status" value="izin_tanpa_keterangan" {{ ($teacherTodayAttendance && $teacherTodayAttendance->status === 'izin_tanpa_keterangan') ? 'checked' : '' }} onchange="toggleTeacherNotes(false)">
                            <strong style="color: #991b1b;">Izin tanpa Keterangan</strong>
                        </label>
                    </div>
                </div>
                <div id="teacherNotesWrap" style="display: {{ ($teacherTodayAttendance && $teacherTodayAttendance->status === 'izin_keterangan') ? 'block' : 'none' }}; margin-bottom: 0.6rem;">
                    <input type="text" name="notes" class="form-control" style="font-size: 0.85rem; padding: 6px 10px;" placeholder="Tulis alasan izin..." value="{{ $teacherTodayAttendance?->notes ?? '' }}">
                </div>
                <button type="submit" class="btn btn-primary btn-sm" style="width: 100%;">
                    {{ $teacherTodayAttendance ? 'Perbarui Presensi Guru' : 'Simpan Presensi Hadir Guru' }}
                </button>
            </form>
        </div>
    </div>

    <!-- Attendance Realtime Summary Card -->
    <div class="card" style="border-left: 4px solid var(--color-primary); box-shadow: var(--shadow-sm);">
        <div class="card-body" style="padding: 1.5rem; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 1.6rem;">⏱️</span>
                        <strong style="font-size: 1.05rem; color: #0f172a;">Presensi Siswa Hari Ini</strong>
                    </div>
                    <span class="badge badge-primary">{{ $attendanceStats['today_formatted'] }}</span>
                </div>
                <p style="font-size: 0.88rem; color: #475569; margin-bottom: 1rem;">
                    Pantau kehadiran realtime seluruh siswa {{ $subjectName }} hari ini:
                </p>
                <div class="grid grid-cols-4" style="gap: 8px; margin-bottom: 1rem;">
                    <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; padding: 10px; text-align: center;">
                        <div style="font-size: 1.4rem; font-weight: 800; color: #065f46;">{{ $attendanceStats['hadir'] }}</div>
                        <div style="font-size: 0.72rem; color: #065f46; font-weight: 600;">Hadir</div>
                    </div>
                    <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 10px; text-align: center;">
                        <div style="font-size: 1.4rem; font-weight: 800; color: #1e40af;">{{ $attendanceStats['izin_keterangan'] }}</div>
                        <div style="font-size: 0.72rem; color: #1e40af; font-weight: 600;">Izin Ket.</div>
                    </div>
                    <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 10px; text-align: center;">
                        <div style="font-size: 1.4rem; font-weight: 800; color: #991b1b;">{{ $attendanceStats['izin_tanpa_keterangan'] }}</div>
                        <div style="font-size: 0.72rem; color: #991b1b; font-weight: 600;">Izin Tanpa Ket.</div>
                    </div>
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px; text-align: center;">
                        <div style="font-size: 1.4rem; font-weight: 800; color: #64748b;">{{ $attendanceStats['belum_absen'] }}</div>
                        <div style="font-size: 0.72rem; color: #64748b; font-weight: 600;">Belum Presensi</div>
                    </div>
                </div>
            </div>
            <div>
                <a href="{{ route('admin.attendance.index') }}" class="btn btn-outline btn-sm" style="width: 100%; border-color: #cbd5e1; color: #1e293b;">
                    Buka Detail Presensi Realtime Tiap Kelas &rarr;
                </a>
            </div>
        </div>
    </div>
</div>

<!-- 5 Core Features Quick Cards -->
<div style="margin-bottom: 2.25rem;">
    <h2 style="font-size: 1.25rem; margin-bottom: 1rem; color: #0f172a; display: flex; align-items: center; gap: 8px;">
        <span>🎯</span> Menu Utama Pengelolaan Kelas {{ $subjectName }}
    </h2>

    <div class="grid grid-cols-3" style="gap: 1.25rem;">
        <!-- 1. Kelola Materi -->
        <div class="card" style="transition: transform 0.2s, box-shadow 0.2s; border-top: 4px solid #2563eb;">
            <div class="card-body" style="padding: 1.25rem; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 0.75rem;">
                        <div style="width: 42px; height: 42px; border-radius: 10px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">
                            📑
                        </div>
                        <div>
                            <h3 style="font-size: 1.05rem; margin: 0; color: #0f172a;">1. Kelola Materi</h3>
                            <span style="font-size: 0.78rem; color: #64748b;">Khusus Sesuai Kelas</span>
                        </div>
                    </div>
                    <p style="font-size: 0.85rem; color: #475569; line-height: 1.45; margin-bottom: 1rem;">
                        @if(auth()->user()->subject_id == 2)
                            Upload & kelola materi slide PPT/PDF khusus tiap grup Bahasa Jepang. Siswa hanya dapat membuka materi grupnya.
                        @elseif(auth()->user()->subject_id == 3)
                            Upload & kelola materi slide PPT/PDF modul pembelajaran Matematika. Siswa hanya dapat membuka materi sesuai tingkatannya.
                        @else
                            Upload & kelola materi slide PPT/PDF khusus tiap kelas. Siswa hanya dapat membuka materi kelas yang diambilnya.
                        @endif
                    </p>
                </div>
                <a href="{{ route('admin.materials.index') }}" class="btn btn-primary btn-sm" style="width: 100%;">
                    Buka Kelola Materi &rarr;
                </a>
            </div>
        </div>

        <!-- 2. Kelola Siswa -->
        <div class="card" style="transition: transform 0.2s, box-shadow 0.2s; border-top: 4px solid #10b981;">
            <div class="card-body" style="padding: 1.25rem; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 0.75rem;">
                        <div style="width: 42px; height: 42px; border-radius: 10px; background: #ecfdf5; color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">
                            👥
                        </div>
                        <div>
                            <h3 style="font-size: 1.05rem; margin: 0; color: #0f172a;">2. Kelola Siswa</h3>
                            <span style="font-size: 0.78rem; color: #64748b;">Nama, Divisi & {{ auth()->user()->subject_id == 2 ? 'Grup' : 'Kelas' }}</span>
                        </div>
                    </div>
                    <p style="font-size: 0.85rem; color: #475569; line-height: 1.45; margin-bottom: 1rem;">
                        Lihat daftar siswa terdaftar {{ $subjectName }}, divisi pekerjaan, dan {{ auth()->user()->subject_id == 2 ? 'grup' : 'kelas' }} yang dipilih. Pantau perkembangan siswa secara berkala.
                    </p>
                </div>
                <a href="{{ route('admin.students.index') }}" class="btn btn-primary btn-sm" style="width: 100%; background: #059669; border-color: #059669;">
                    Buka Kelola Siswa &rarr;
                </a>
            </div>
        </div>

        <!-- 3. Kelola Absensi -->
        <div class="card" style="transition: transform 0.2s, box-shadow 0.2s; border-top: 4px solid #f59e0b;">
            <div class="card-body" style="padding: 1.25rem; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 0.75rem;">
                        <div style="width: 42px; height: 42px; border-radius: 10px; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">
                            📊
                        </div>
                        <div>
                            <h3 style="font-size: 1.05rem; margin: 0; color: #0f172a;">3. Kelola Absensi</h3>
                            <span style="font-size: 0.78rem; color: #64748b;">Realtime Per {{ auth()->user()->subject_id == 2 ? 'Grup' : 'Kelas' }}</span>
                        </div>
                    </div>
                    <p style="font-size: 0.85rem; color: #475569; line-height: 1.45; margin-bottom: 1rem;">
                        Pantau absensi harian siswa {{ $subjectName }} secara realtime per {{ auth()->user()->subject_id == 2 ? 'grup' : 'kelas' }}. Dilengkapi filter tanggal dan status kehadiran.
                    </p>
                </div>
                <a href="{{ route('admin.attendance.index') }}" class="btn btn-warning btn-sm" style="width: 100%;">
                    Buka Presensi Realtime &rarr;
                </a>
            </div>
        </div>

        @if(auth()->user()->subject_id == 1)
            <!-- 4. Kelola Nilai & Feedback (Khusus Guru Bahasa Inggris) -->
            <div class="card" style="transition: transform 0.2s, box-shadow 0.2s; border-top: 4px solid #8b5cf6;">
                <div class="card-body" style="padding: 1.25rem; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 0.75rem;">
                            <div style="width: 42px; height: 42px; border-radius: 10px; background: #f5f3ff; color: #7c3aed; display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">
                                ⭐
                            </div>
                            <div>
                                <h3 style="font-size: 1.05rem; margin: 0; color: #0f172a;">4. Kelola Nilai & Feedback</h3>
                                <span style="font-size: 0.78rem; color: #64748b;">Format Sesuai Absensi.xlsx</span>
                            </div>
                        </div>
                        <p style="font-size: 0.85rem; color: #475569; line-height: 1.45; margin-bottom: 1rem;">
                            Input manual nilai Meeting (1-4), Exam (Fluency, Grammar, Pronunciation, Vocabulary), Feedback, serta unduh rekap hasil penilaian Excel (Bulan 1 - 5).
                        </p>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <a href="{{ route('admin.grades.index') }}" class="btn btn-sm" style="flex: 1; background: #7c3aed; color: #fff; border-color: #7c3aed;">
                            Kelola Nilai &rarr;
                        </a>
                        <a href="{{ route('admin.grades.export', ['class_name' => 'all', 'month' => 'all']) }}" class="btn btn-sm btn-outline-success" style="border-color: #10b981; color: #059669; font-weight: 700;" title="Unduh Excel Semua Bulan">
                            📥 Excel
                        </a>
                    </div>
                </div>
            </div>
        @elseif(auth()->user()->subject_id == 2)
            <!-- 4. Tes Evaluasi Siswa (Khusus Guru Bahasa Jepang) -->
            <div class="card" style="transition: transform 0.2s, box-shadow 0.2s; border-top: 4px solid #db2777;">
                <div class="card-body" style="padding: 1.25rem; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 0.75rem;">
                            <div style="width: 42px; height: 42px; border-radius: 10px; background: #fdf2f8; color: #db2777; display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">
                                📝
                            </div>
                            <div>
                                <h3 style="font-size: 1.05rem; margin: 0; color: #0f172a;">4. Tes Evaluasi Siswa</h3>
                                <span style="font-size: 0.78rem; color: #64748b;">Per 4 Pertemuan (4, 8, 12, dst)</span>
                            </div>
                        </div>
                        <p style="font-size: 0.85rem; color: #475569; line-height: 1.45; margin-bottom: 1rem;">
                            Kelola butir soal evaluasi berkala per 4 pertemuan, atur batas nilai kelulusan, dan tinjau riwayat nilai pengerjaan seluruh siswa.
                        </p>
                    </div>
                    <a href="{{ route('admin.japanese.tests.index') }}" class="btn btn-sm" style="width: 100%; background: #db2777; color: #fff; border-color: #db2777; font-weight: 700;">
                        Kelola Tes Evaluasi &rarr;
                    </a>
                </div>
            </div>
        @else
            <!-- 4. Kelola Bank Soal Latihan (Khusus Guru Matematika) -->
            <div class="card" style="transition: transform 0.2s, box-shadow 0.2s; border-top: 4px solid #059669;">
                <div class="card-body" style="padding: 1.25rem; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 0.75rem;">
                            <div style="width: 42px; height: 42px; border-radius: 10px; background: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">
                                📊
                            </div>
                            <div>
                                <h3 style="font-size: 1.05rem; margin: 0; color: #0f172a;">4. Bank Soal Latihan</h3>
                                <span style="font-size: 0.78rem; color: #64748b;">10 Butir Soal per Tingkatan</span>
                            </div>
                        </div>
                        <p style="font-size: 0.85rem; color: #475569; line-height: 1.45; margin-bottom: 1rem;">
                            Kelola butir soal pilihan ganda latihan matematika, kunci jawaban, dan pembahasan untuk setiap tingkatan/level pembelajaran.
                        </p>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <a href="{{ route('admin.exercises.index') }}" class="btn btn-sm" style="flex: 1; background: #059669; color: #fff; border-color: #059669; font-weight: 700;">
                            Kelola Bank Soal &rarr;
                        </a>
                        <a href="{{ route('admin.exercises.create') }}" class="btn btn-sm btn-outline-success" style="border-color: #059669; color: #059669; font-weight: 700;">
                            + Buat Soal
                        </a>
                    </div>
                </div>
            </div>
        @endif

        <!-- 5. Kelola Kelas / Grup & Profil Guru -->
        <div class="card" style="transition: transform 0.2s, box-shadow 0.2s; border-top: 4px solid #0284c7;">
            <div class="card-body" style="padding: 1.25rem; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 0.75rem;">
                        <div style="width: 42px; height: 42px; border-radius: 10px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">
                            🏫
                        </div>
                        <div>
                            @if(auth()->user()->subject_id == 2)
                                <h3 style="font-size: 1.05rem; margin: 0; color: #0f172a;">5. Kelola Grup & Profil</h3>
                                <span style="font-size: 0.78rem; color: #64748b;">Grup Belajar & Profil Guru</span>
                            @elseif(auth()->user()->subject_id == 3)
                                <h3 style="font-size: 1.05rem; margin: 0; color: #0f172a;">5. Kelola Kelas & Pertemuan</h3>
                                <span style="font-size: 0.78rem; color: #64748b;">Kelas & Modul Pertemuan</span>
                            @else
                                <h3 style="font-size: 1.05rem; margin: 0; color: #0f172a;">5. Kelola Kelas & Profil</h3>
                                <span style="font-size: 0.78rem; color: #64748b;">Nama Kelas & Profil Guru</span>
                            @endif
                        </div>
                    </div>
                    <p style="font-size: 0.85rem; color: #475569; line-height: 1.45; margin-bottom: 1rem;">
                        @if(auth()->user()->subject_id == 2)
                            Atur nama-nama grup Bahasa Jepang, tambah grup baru, serta perbarui profil guru.
                        @elseif(auth()->user()->subject_id == 3)
                            Atur kelas belajar matematika, kelola modul pertemuan pembelajaran, serta perbarui profil guru.
                        @else
                            Atur nama-nama kelas Bahasa Inggris, tambah kelas baru, serta perbarui profil guru.
                        @endif
                    </p>
                </div>
                @if(auth()->user()->subject_id == 3)
                    <div style="display: flex; gap: 8px;">
                        <a href="{{ route('admin.meetings.index') }}" class="btn btn-secondary btn-sm" style="flex: 1; text-align: center; font-weight: 700;">
                            Pertemuan
                        </a>
                        <a href="{{ route('admin.classes.index') }}" class="btn btn-outline btn-sm" style="border-color: #cbd5e1; font-weight: 700;">
                            Kelas & Profil
                        </a>
                    </div>
                @else
                    <a href="{{ route('admin.classes.index') }}" class="btn btn-secondary btn-sm" style="width: 100%; text-align: center; font-weight: 700;">
                        {{ auth()->user()->subject_id == 2 ? 'Kelola Grup & Profil' : 'Kelola Kelas & Profil' }}
                    </a>
                @endif
            </div>
        </div>

        <!-- Quick Summary Stats -->
        <div class="card" style="background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%); border-top: 4px solid #64748b;">
            <div class="card-body" style="padding: 1.25rem; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="font-weight: 700; color: #1e293b; margin-bottom: 0.75rem; font-size: 1rem;">
                        📈 Ringkasan Data Learning Musashi
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 8px; font-size: 0.88rem;">
                        <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed #cbd5e1; padding-bottom: 4px;">
                            <span style="color: #64748b;">Total Siswa:</span>
                            <strong>{{ $stats['total_students'] }} Orang</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed #cbd5e1; padding-bottom: 4px;">
                            <span style="color: #64748b;">Total {{ auth()->user()->subject_id == 2 ? 'Grup' : 'Kelas' }}:</span>
                            <strong>{{ $stats['total_classes'] ?? 0 }} {{ auth()->user()->subject_id == 2 ? 'Grup' : 'Kelas' }}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed #cbd5e1; padding-bottom: 4px;">
                            <span style="color: #64748b;">Materi Slide Diunggah:</span>
                            <strong>{{ $stats['total_materials'] }} File</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding-bottom: 4px;">
                            @if(auth()->user()->subject_id == 1)
                                <span style="color: #64748b;">Total Nilai Tercatat:</span>
                                <strong>{{ $stats['total_grades'] ?? 0 }} Rekap</strong>
                            @elseif(auth()->user()->subject_id == 2)
                                <span style="color: #64748b;">Total Ujian Evaluasi:</span>
                                <strong>{{ $stats['total_tests'] ?? 0 }} Tes</strong>
                            @else
                                <span style="color: #64748b;">Total Soal Latihan:</span>
                                <strong>{{ $stats['total_exercises'] ?? 0 }} Butir</strong>
                            @endif
                        </div>
                    </div>
                </div>
                <div style="font-size: 0.78rem; color: #64748b; margin-top: 0.5rem; text-align: center;">
                    Sistem siap & terintegrasi penuh.
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Students Table -->
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div>
            <h3 style="font-size: 1.15rem; margin: 0;">
                👥 Siswa {{ $subjectName }} Terbaru
            </h3>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin: 2px 0 0 0;">
                Siswa yang terdaftar dalam pembelajaran {{ $subjectName }}
            </p>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('admin.students.create') }}" class="btn btn-outline btn-sm">+ Tambah Siswa</a>
            <a href="{{ route('admin.students.index') }}" class="btn btn-secondary btn-sm">Lihat Semua Siswa &rarr;</a>
        </div>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama Siswa</th>
                        <th>Divisi</th>
                        <th>{{ auth()->user()->subject_id == 2 ? 'Grup' : 'Kelas' }} {{ $subjectName }}</th>
                        <th>Email Akun</th>
                        <th>Status</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentStudents as $student)
                        <tr>
                            <td>
                                <strong>{{ $student->name }}</strong>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $student->phone ?? '-' }}</div>
                            </td>
                            <td>
                                <span class="badge" style="background: #f1f5f9; color: #334155; font-weight: 600;">
                                    🏢 {{ $student->division ?? 'Umum' }}
                                </span>
                            </td>
                            <td>
                                @if($student->class_name)
                                    <span class="badge" style="background: {{ auth()->user()->subject_id == 2 ? '#fdf2f8' : (auth()->user()->subject_id == 3 ? '#ecfdf5' : '#eff6ff') }}; color: {{ auth()->user()->subject_id == 2 ? '#be185d' : (auth()->user()->subject_id == 3 ? '#047857' : '#1d4ed8') }}; font-weight: 700; border: 1px solid {{ auth()->user()->subject_id == 2 ? '#fbcfe8' : (auth()->user()->subject_id == 3 ? '#a7f3d0' : '#bfdbfe') }};">
                                        🏷️ {{ $student->class_name }}
                                    </span>
                                @else
                                    <span class="badge badge-neutral">-</span>
                                @endif
                            </td>
                            <td>{{ $student->email }}</td>
                            <td>
                                <span class="badge {{ $student->status === 'active' ? 'badge-success' : 'badge-danger' }}">
                                    {{ $student->status === 'active' ? 'Aktif' : 'Non-Aktif' }}
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: flex; justify-content: flex-end; gap: 6px;">
                                    @if(auth()->user()->subject_id == 1 && $student->class_name)
                                        <a href="{{ route('admin.grades.index', ['class_name' => $student->class_name]) }}" class="btn btn-warning btn-sm" title="Beri Nilai">
                                            ⭐ Nilai
                                        </a>
                                    @endif
                                    <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-secondary btn-sm" title="Edit Data Siswa">
                                        ✏️ Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                                Belum ada siswa terdaftar pada pembelajaran {{ $subjectName }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function toggleTeacherNotes(show) {
    const wrap = document.getElementById('teacherNotesWrap');
    if (wrap) {
        wrap.style.display = show ? 'block' : 'none';
        if (show) {
            const input = wrap.querySelector('input');
            if (input) input.focus();
        }
    }
}

function updateTeacherDashClock() {
    const clockEl = document.getElementById('teacherDashClock');
    if (clockEl) {
        const now = new Date();
        const optionsTime = { timeZone: 'Asia/Jakarta', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
        clockEl.innerText = new Intl.DateTimeFormat('id-ID', optionsTime).format(now).replace(/\./g, ':');
    }
}
setInterval(updateTeacherDashClock, 1000);
</script>
@endsection
