@extends('layouts.app')

@section('title', 'Profil Saya & Pengaturan Akun - Musashi Learning')

@section('content')
@php
    $studentSubjectId = $user->subject_id ?? 1;
    $themeColor = match((int)$studentSubjectId) {
        2 => '#db2777', // Japanese Pink
        3 => '#059669', // Math Emerald
        default => '#2563eb', // English Blue
    };
    $themeGradient = match((int)$studentSubjectId) {
        2 => 'linear-gradient(135deg, #831843 0%, #be185d 100%)',
        3 => 'linear-gradient(135deg, #064e3b 0%, #059669 100%)',
        default => 'linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%)',
    };
    $badgeSubject = match((int)$studentSubjectId) {
        2 => 'Siswa Bahasa Jepang',
        3 => 'Siswa Matematika',
        default => 'Siswa Bahasa Inggris',
    };
@endphp

<div style="margin-bottom: 2rem;">
    <a href="{{ route('student.dashboard') }}" class="btn btn-secondary btn-sm" style="margin-bottom: 0.75rem; display: inline-flex; align-items: center; gap: 6px;">
        &larr; Kembali ke Beranda Belajar
    </a>
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-size: 1.85rem; margin: 0; font-weight: 800; color: #0f172a;">
                👤 Profil Saya & Pengaturan Akun
            </h1>
            <p style="color: var(--text-muted); font-size: 0.94rem; margin-top: 4px;">
                Lihat informasi data diri Anda dan perbarui alamat email atau kata sandi akun secara mandiri.
            </p>
        </div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <a href="{{ route('student.attendance.index') }}" class="btn btn-secondary btn-sm" style="font-weight: 700;">
                📅 Riwayat Absensi
            </a>
            @if($studentSubjectId == 2)
                <a href="{{ route('student.japanese.grades.index') }}" class="btn btn-secondary btn-sm" style="font-weight: 700; color: #db2777;">
                    📊 Riwayat Skor Tes
                </a>
            @elseif($studentSubjectId == 1)
                <a href="{{ route('student.grades.index') }}" class="btn btn-secondary btn-sm" style="font-weight: 700; color: #2563eb;">
                    📊 Riwayat Skor Latihan
                </a>
            @endif
        </div>
    </div>
</div>

<div class="grid grid-cols-3" style="gap: 1.75rem; align-items: flex-start;">
    <!-- Left Column: Student Identity Card -->
    <div style="grid-column: span 1;">
        <div class="card" style="box-shadow: var(--shadow-md); border-radius: var(--radius-xl); overflow: hidden; border: 1px solid #e2e8f0;">
            <!-- Header Pattern -->
            <div style="background: {{ $themeGradient }}; height: 110px; position: relative;">
                <span class="badge" style="position: absolute; top: 14px; right: 14px; background: rgba(255,255,255,0.25); color: #ffffff; font-weight: 700; font-size: 0.78rem;">
                    {{ $badgeSubject }}
                </span>
            </div>

            <!-- Profile Info Body -->
            <div class="card-body" style="padding: 0 1.5rem 1.75rem; position: relative; margin-top: -50px; text-align: center;">
                <div class="avatar" style="width: 90px; height: 90px; font-size: 2.2rem; font-weight: 800; background: #ffffff; color: {{ $themeColor }}; border: 4px solid #ffffff; box-shadow: var(--shadow-md); border-radius: 50%; margin: 0 auto 12px; display: flex; align-items: center; justify-content: center;">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>

                <h3 style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0;">
                    {{ $user->name }}
                </h3>
                <div style="color: #64748b; font-size: 0.85rem; margin-top: 3px; word-break: break-all;">
                    {{ $user->email }}
                </div>

                <div style="display: flex; justify-content: center; gap: 6px; margin-top: 10px; flex-wrap: wrap;">
                    <span class="badge" style="background: #eff6ff; color: #1e40af; font-size: 0.78rem; font-weight: 700; padding: 4px 10px;">
                        🎓 {{ $user->class_name ?: 'Belum Ada Kelas' }}
                    </span>
                    <span class="badge badge-success" style="font-size: 0.78rem; font-weight: 700; padding: 4px 10px;">
                        Aktif
                    </span>
                </div>

                <!-- Academic Data Table -->
                <div style="text-align: left; margin-top: 1.5rem; border-top: 1px solid #f1f5f9; padding-top: 1.25rem;">
                    <div style="font-size: 0.76rem; text-transform: uppercase; font-weight: 800; color: #94a3b8; letter-spacing: 0.5px; margin-bottom: 12px;">
                        Informasi Akademik
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 10px; font-size: 0.88rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: #64748b;">Mata Pelajaran:</span>
                            <strong style="color: #0f172a;">{{ $subjectName }}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: #64748b;">{{ $studentSubjectId == 2 ? 'Grup Belajar:' : 'Kelas Belajar:' }}</span>
                            <strong style="color: {{ $themeColor }}; font-weight: 800;">{{ $user->class_name ?: '-' }}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: #64748b;">Divisi Perusahaan:</span>
                            <strong style="color: #0f172a;">{{ $user->division ?: '-' }}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: #64748b;">ID Siswa:</span>
                            <span style="color: #64748b; font-family: monospace; font-weight: 600;">#{{ $user->id }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: #64748b;">Terdaftar Sejak:</span>
                            <span style="color: #0f172a; font-weight: 600;">{{ $user->created_at->format('d M Y') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Fast Links -->
                <div style="margin-top: 1.5rem; border-top: 1px solid #f1f5f9; padding-top: 1.25rem; display: flex; flex-direction: column; gap: 8px;">
                    <a href="{{ route('student.materials.index') }}" class="btn btn-secondary btn-sm" style="width: 100%; justify-content: center; font-weight: 700;">
                        📚 Buka Materi {{ $studentSubjectId == 2 ? 'Grup' : 'Kelas' }}
                    </a>
                    @if($studentSubjectId == 2)
                        <a href="{{ route('student.japanese.tests.index') }}" class="btn btn-secondary btn-sm" style="width: 100%; justify-content: center; font-weight: 700; color: #db2777;">
                            📝 Ikuti Latihan / Tes Evaluasi
                        </a>
                    @else
                        <a href="{{ route('student.exercises.index') }}" class="btn btn-secondary btn-sm" style="width: 100%; justify-content: center; font-weight: 700; color: #2563eb;">
                            📝 Latihan Soal Pembelajaran
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Edit Profile & Password Form -->
    <div style="grid-column: span 2;">
        <div class="card" style="box-shadow: var(--shadow-md); border-radius: var(--radius-xl); border: 1px solid #e2e8f0; overflow: hidden;">
            <div class="card-header" style="padding: 1.25rem 1.75rem; background: #ffffff; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 style="font-size: 1.18rem; margin: 0; font-weight: 800; color: #0f172a;">
                        ✏️ Formulir Edit Data Diri & Kata Sandi
                    </h3>
                    <p style="font-size: 0.84rem; color: var(--text-muted); margin: 3px 0 0;">
                        Perubahan akan langsung berlaku setelah Anda menekan tombol simpan.
                    </p>
                </div>
                <span class="badge" style="background: #f1f5f9; color: #475569; font-weight: 700;">
                    Keamanan Akun
                </span>
            </div>

            <div class="card-body" style="padding: 1.75rem;">
                @if ($errors->any())
                    <div class="alert alert-danger" style="margin-bottom: 1.5rem;">
                        <ul style="margin: 0; padding-left: 1.25rem;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('student.profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Section 1: Data Identitas Diri -->
                    <div style="margin-bottom: 1.5rem;">
                        <div style="font-size: 0.82rem; text-transform: uppercase; font-weight: 800; color: {{ $themeColor }}; letter-spacing: 0.5px; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                            <span>1. Data Pribadi & Kontak</span>
                        </div>

                        <div class="form-group" style="margin-bottom: 1.25rem;">
                            <label class="form-label" for="name" style="font-weight: 700; color: #1e293b;">
                                Nama Lengkap Siswa *
                            </label>
                            <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $user->name) }}" required placeholder="Contoh: Tanaka Musashi" style="font-size: 0.95rem; font-weight: 600;">
                            <small class="form-text">Nama ini akan digunakan pada lembar presensi dan sertifikat penilaian.</small>
                        </div>

                        <div class="form-group" style="margin-bottom: 1.25rem;">
                            <label class="form-label" for="email" style="font-weight: 700; color: #1e293b;">
                                Alamat Email Login *
                            </label>
                            <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $user->email) }}" required placeholder="contoh@musashi.id" style="font-size: 0.95rem; font-weight: 600;">
                            <small class="form-text">Gunakan alamat email aktif. Anda akan menggunakan email ini saat masuk ke website.</small>
                        </div>
                    </div>

                    <!-- Section 2: Informasi Akademik Terdaftar (Read-Only) -->
                    <div style="margin-bottom: 1.75rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: var(--radius-md); padding: 1.25rem;">
                        <div style="font-size: 0.82rem; text-transform: uppercase; font-weight: 800; color: #64748b; letter-spacing: 0.5px; margin-bottom: 10px;">
                            2. Kelas & Mata Pelajaran Terdaftar
                        </div>

                        <div class="grid grid-cols-2" style="gap: 1rem;">
                            <div>
                                <label style="font-size: 0.8rem; font-weight: 700; color: #64748b; display: block; margin-bottom: 4px;">
                                    Mata Pelajaran:
                                </label>
                                <input type="text" class="form-control" value="{{ $subjectName }}" disabled style="background: #ffffff; color: #334155; font-weight: 700;">
                            </div>
                            <div>
                                <label style="font-size: 0.8rem; font-weight: 700; color: #64748b; display: block; margin-bottom: 4px;">
                                    {{ $studentSubjectId == 2 ? 'Grup Siswa:' : 'Kelas Siswa:' }}
                                </label>
                                <input type="text" class="form-control" value="{{ $user->class_name ?: 'Belum Ada Kelas' }}" disabled style="background: #ffffff; color: {{ $themeColor }}; font-weight: 800;">
                            </div>
                        </div>
                        <small style="font-size: 0.76rem; color: #94a3b8; display: block; margin-top: 8px;">
                            💡 Data kelas dan mata pelajaran dikelola langsung oleh sistem dan Pengajar Anda.
                        </small>
                    </div>

                    <!-- Section 3: Keamanan & Ganti Password -->
                    <div style="margin-bottom: 2rem; border-top: 1px solid #e2e8f0; padding-top: 1.5rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <div style="font-size: 0.82rem; text-transform: uppercase; font-weight: 800; color: #0f172a; letter-spacing: 0.5px; display: flex; align-items: center; gap: 6px;">
                                <span>🔐 3. Ganti Kata Sandi (Password)</span>
                            </div>
                            <span class="badge" style="background: #fef3c7; color: #92400e; font-size: 0.72rem; font-weight: 700;">
                                Opsional
                            </span>
                        </div>
                        <p style="font-size: 0.84rem; color: var(--text-muted); margin: 0 0 14px;">
                            Kosongkan kedua kolom di bawah jika Anda <strong>tidak ingin</strong> mengganti kata sandi.
                        </p>

                        <div class="grid grid-cols-2" style="gap: 1.25rem;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" for="password" style="font-weight: 700; color: #1e293b;">
                                    Password Baru
                                </label>
                                <input type="password" name="password" id="password" class="form-control" placeholder="Minimal 6 karakter" autocomplete="new-password">
                            </div>

                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" for="password_confirmation" style="font-weight: 700; color: #1e293b;">
                                    Konfirmasi Password Baru
                                </label>
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Ulangi password baru" autocomplete="new-password">
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div style="display: flex; justify-content: flex-end; align-items: center; gap: 12px; border-top: 1px solid #e2e8f0; padding-top: 1.25rem;">
                        <a href="{{ route('student.dashboard') }}" class="btn btn-secondary">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary" style="font-weight: 800; padding: 10px 28px; background: {{ $themeColor }}; border-color: {{ $themeColor }};">
                            💾 Simpan Perubahan Profil
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
