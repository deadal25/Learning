@extends('layouts.app')

@section('title', 'Kelola Siswa - Super Administrator Musashi')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <div class="badge badge-warning" style="margin-bottom: 0.5rem; font-weight: 800;">
            Super Admin Data Center
        </div>
        <h1 style="font-size: 1.9rem; font-weight: 800; margin-bottom: 0.35rem; color: #0f172a;">
            Kelola Siswa Platform
        </h1>
        <p style="color: var(--text-muted); font-size: 0.95rem; max-width: 650px;">
            Pusat manajemen akun pelajar seluruh mata pelajaran (Bahasa Inggris, Bahasa Jepang, dan Matematika). Dilengkapi fitur import Excel, tambah siswa manual, dan hapus semua siswa.
        </p>
    </div>

    <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
        <button type="button" class="btn btn-secondary" onclick="openImportModal()" style="font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
            <span>📥</span> Import File Excel
        </button>
        <a href="{{ route('superadmin.students.create', ['subject_id' => $currentSubject->id]) }}" class="btn btn-primary" style="font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
            <span>+</span> Tambah Siswa Manual
        </a>
        <button type="button" class="btn btn-danger" onclick="openDeleteAllModal()" style="font-weight: 700; display: inline-flex; align-items: center; gap: 6px;" title="Hapus semua siswa dari sistem atau mata pelajaran ini">
            <span>🗑️</span> Hapus Semua Siswa
        </button>
    </div>
</div>

<!-- Alert Notifikasi Flash -->
@if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 1.25rem;">
        {!! session('success') !!}
    </div>
@endif

@if(session('warning'))
    <div class="alert alert-warning" style="margin-bottom: 1.25rem;">
        {!! session('warning') !!}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger" style="margin-bottom: 1.25rem;">
        {!! session('error') !!}
    </div>
@endif

<!-- Subject Selector Tabs -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 220px), 1fr)); gap: 14px; margin-bottom: 1.5rem;">
    @foreach($subjects as $subj)
        @php
            $isSelected = ($currentSubject->id === $subj->id);
            $subTitle = match($subj->id) {
                1 => 'Kelas I1 - I6, B1 - B8, E1',
                2 => 'Grup 1 - Grup 12',
                3 => 'Kelas MTK Dasar & Terapan',
                default => 'Daftar Kelas'
            };
        @endphp
        <a href="{{ route('superadmin.students.index', ['subject_id' => $subj->id]) }}"
           style="text-decoration: none; color: inherit; background: {{ $isSelected ? '#ffffff' : '#f8fafc' }}; border: 2px solid {{ $isSelected ? 'var(--primary)' : 'var(--border-color)' }}; border-radius: var(--radius-lg); padding: 14px 18px; display: flex; align-items: center; justify-content: space-between; box-shadow: {{ $isSelected ? '0 8px 20px rgba(37,99,235,0.12)' : 'none' }}; transition: all 0.2s ease;">
            <div>
                <div style="font-weight: 800; font-size: 1.05rem; color: {{ $isSelected ? 'var(--primary)' : '#0f172a' }};">
                    {{ $subj->name }}
                </div>
                <div style="font-size: 0.76rem; color: var(--text-muted); margin-top: 2px;">
                    {{ $subTitle }}
                </div>
            </div>
            <div style="text-align: right;">
                <span class="badge" style="font-size: 0.85rem; font-weight: 800; background: {{ $isSelected ? 'var(--primary)' : '#e2e8f0' }}; color: {{ $isSelected ? '#fff' : '#475569' }}; padding: 4px 10px; border-radius: 9999px;">
                    {{ $subjectCounts[$subj->id] ?? 0 }} Siswa
                </span>
            </div>
        </a>
    @endforeach
</div>

@if($currentSubject->id === 1 && $hasDefaultEnglishFile)
    <!-- Special Banner for default Bahasa Inggris Siswa.xlsx / inggris.xlsx -->
    <div style="background: linear-gradient(135deg, #eff6ff 0%, #f0fdf4 100%); border: 1px solid #bfdbfe; border-radius: var(--radius-lg); padding: 16px 20px; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
        <div style="display: flex; align-items: center; gap: 14px;">
            <div style="font-size: 2.2rem; background: #fff; width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: var(--shadow-sm);">
                📊
            </div>
            <div>
                <strong style="color: #1e3a8a; font-size: 1rem; display: block;">
                    Tersedia Berkas Siswa Bawaan: <code>public/images/{{ $defaultEnglishFileName ?? 'Bahasa Inggris Siswa.xlsx' }}</code>
                </strong>
                <span style="color: #475569; font-size: 0.85rem;">
                    Terdapat <strong>111 data peserta Bahasa Inggris</strong> (Kelas I1 - I6, B1 - B8, E1 beserta NRP & Bagian) siap disinkronkan ke guru pengajar pilihan Anda.
                </span>
            </div>
        </div>
        <form action="{{ route('superadmin.students.import-default-english') }}" method="POST" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;" onsubmit="return confirm('Apakah Anda yakin ingin mengimpor / memperbarui 111 data siswa dari file {{ $defaultEnglishFileName ?? 'Bahasa Inggris Siswa.xlsx' }}?')">
            @csrf
            <div style="display: flex; align-items: center; gap: 6px;">
                <label for="banner_teacher_id" style="font-size: 0.84rem; font-weight: 700; color: #1e3a8a; margin: 0; white-space: nowrap;">
                    Guru Pengajar:
                </label>
                <select name="teacher_id" id="banner_teacher_id" class="form-control" style="min-width: 200px; font-weight: 600; background: #ffffff; border-color: #93c5fd;">
                    @foreach($englishTeachers as $t)
                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="font-weight: 700; background: #059669; border-color: #059669; box-shadow: 0 4px 12px rgba(5,150,105,0.25);">
                ⚡ Import 111 Siswa Bahasa Inggris
            </button>
        </form>
    </div>
@endif

<!-- Filter Bar -->
<div class="card" style="margin-bottom: 1.5rem; box-shadow: var(--shadow-sm);">
    <div class="card-body" style="padding: 1rem 1.5rem;">
        <form method="GET" action="{{ route('superadmin.students.index') }}" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <input type="hidden" name="subject_id" value="{{ $currentSubject->id }}">

            <div style="min-width: 170px;">
                <select name="class_name" class="form-control" onchange="this.form.submit()" style="font-weight: 600;">
                    <option value="all">-- Semua Kelas / Grup --</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->name }}" {{ ($selectedClass ?? '') === $c->name ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            @if($currentSubject->id === 1 && $englishTeachers->count() > 0)
                <div style="min-width: 190px;">
                    <select name="teacher_id" class="form-control" onchange="this.form.submit()" style="font-weight: 600;">
                        <option value="">-- Semua Guru Pengajar --</option>
                        @foreach($englishTeachers as $t)
                            <option value="{{ $t->id }}" {{ (string)request('teacher_id') === (string)$t->id ? 'selected' : '' }}>
                                👨‍🏫 {{ $t->name }}
                            </option>
                        @endforeach
                        <option value="unassigned" {{ request('teacher_id') === 'unassigned' ? 'selected' : '' }}>
                            ⚠️ (Belum Ada Guru)
                        </option>
                    </select>
                </div>
            @endif

            <div style="flex: 1; min-width: 200px;">
                <input type="text" name="search" class="form-control" placeholder="Cari nama siswa, NRP, email, bagian..." value="{{ request('search') }}">
            </div>

            <div style="min-width: 130px;">
                <select name="status" class="form-control" onchange="this.form.submit()" style="font-weight: 600;">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>🟢 Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>🔴 Non-Aktif</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary" style="font-weight: 700;">Filter Data</button>
            @if(request()->hasAny(['search', 'class_name', 'status', 'teacher_id']))
                <a href="{{ route('superadmin.students.index', ['subject_id' => $currentSubject->id]) }}" class="btn btn-secondary" style="color: var(--text-muted);">Reset</a>
            @endif
        </form>
    </div>
</div>

<!-- Table Card -->
<div class="card" style="box-shadow: var(--shadow-sm);">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; background: #ffffff; border-bottom: 1px solid var(--border-color); padding: 1rem 1.5rem; flex-wrap: wrap; gap: 8px;">
        <h3 style="font-size: 1.1rem; font-weight: 800; margin: 0; color: #0f172a;">
            Daftar Siswa {{ $currentSubject->name }} ({{ $students->total() }} Pelajar)
        </h3>
        <span style="font-size: 0.85rem; color: var(--text-muted);">
            Menampilkan halaman {{ $students->currentPage() }} dari {{ $students->lastPage() ?: 1 }}
        </span>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table" style="vertical-align: middle;">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">No</th>
                        <th>Nama Pelajar & NRP</th>
                        <th>Email Akun</th>
                        <th>Kata Sandi (Password)</th>
                        <th>Nama Kelas & Grup</th>
                        <th>Bagian / Divisi</th>
                        <th style="text-align: center; width: 110px;">Status</th>
                        <th style="text-align: right; width: 150px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $index => $student)
                        @php
                            $cleanNrp = $student->nrp ? preg_replace('/[^a-zA-Z0-9]/', '', $student->nrp) : '';
                            $studentPwd = $cleanNrp ? "{$cleanNrp}@musashi" : 'password';
                        @endphp
                        <tr>
                            <td style="text-align: center; font-weight: 600; color: #64748b;">
                                {{ $students->firstItem() + $index }}
                            </td>
                            <td>
                                <div>
                                    <strong style="color: #0f172a; font-size: 0.95rem;">{{ $student->name }}</strong>
                                    @if($student->nrp)
                                        <div style="font-size: 0.76rem; color: #475569; font-weight: 700; font-family: monospace; display: flex; align-items: center; gap: 4px; margin-top: 2px;">
                                            <span style="background: #e2e8f0; color: #1e293b; padding: 1px 6px; border-radius: 4px;">NRP: {{ $student->nrp }}</span>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <span style="font-size: 0.85rem; color: #334155; font-family: monospace;">
                                        {{ $student->email }}
                                    </span>
                                    <button type="button" onclick="navigator.clipboard.writeText('{{ $student->email }}'); alert('Email {{ $student->email }} disalin!');" title="Salin Email" style="background: none; border: none; cursor: pointer; padding: 2px; font-size: 0.8rem; opacity: 0.6;">
                                        📋
                                    </button>
                                </div>
                            </td>
                            <td>
                                <div style="display: inline-flex; align-items: center; gap: 6px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 3px 8px;">
                                    <span style="font-size: 0.82rem;">🔑</span>
                                    <span style="font-family: monospace; font-weight: 700; color: #166534; font-size: 0.85rem;">{{ $studentPwd }}</span>
                                    <button type="button" onclick="navigator.clipboard.writeText('{{ $studentPwd }}'); alert('Kata sandi {{ $studentPwd }} disalin!');" title="Salin Sandi" style="background: none; border: none; cursor: pointer; padding: 0 2px; font-size: 0.8rem; color: #15803d;">
                                        📋
                                    </button>
                                </div>
                            </td>
                            <td>
                                @if($student->class_name)
                                    <span class="badge badge-primary" style="font-size: 0.82rem; font-weight: 700; padding: 4px 10px;">
                                        {{ $student->class_name }}
                                    </span>
                                @else
                                    <span class="badge" style="background: #f1f5f9; color: #64748b; font-size: 0.78rem;">
                                        Belum Ditentukan
                                    </span>
                                @endif

                                @if($currentSubject->id === 1)
                                    @php
                                        $tEnroll = $student->enrollments->firstWhere('subject_id', 1)?->teacher ?? $student->creator;
                                    @endphp
                                    <div style="margin-top: 5px;">
                                        @if($tEnroll)
                                            <span class="badge" style="background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; font-size: 0.74rem; font-weight: 600; padding: 2px 7px;">
                                                👨‍🏫 {{ $tEnroll->name }}
                                            </span>
                                        @else
                                            <span class="badge" style="background: #fff7ed; color: #9a3412; border: 1px solid #fed7aa; font-size: 0.72rem; font-style: italic; padding: 2px 6px;">
                                                Belum ada guru
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span style="font-size: 0.85rem; color: #475569;">
                                    {{ $student->division ?: '-' }}
                                </span>
                            </td>
                            <td style="text-align: center;">
                                @if($student->status === 'active')
                                    <span class="badge badge-success" style="font-weight: 700; padding: 4px 10px;">
                                        Aktif
                                    </span>
                                @else
                                    <span class="badge badge-danger" style="font-weight: 700; padding: 4px 10px;">
                                        Non-Aktif
                                    </span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 6px;">
                                    <a href="{{ route('superadmin.students.edit', $student) }}" class="btn btn-secondary btn-sm" title="Edit Akun Siswa">
                                        Edit
                                    </a>
                                    <form action="{{ route('superadmin.students.destroy', $student) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun siswa \'{{ $student->name }}\'?')" style="margin: 0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus Siswa">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 3.5rem 1.5rem; color: var(--text-muted);">
                                <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">🎒</div>
                                <div style="font-weight: 700; font-size: 1.1rem; color: #0f172a; margin-bottom: 0.25rem;">
                                    Belum Ada Siswa Terdaftar
                                </div>
                                <p style="max-width: 480px; margin: 0 auto 1.25rem auto; font-size: 0.9rem;">
                                    Belum ada data siswa untuk mata pelajaran <strong>{{ $currentSubject->name }}</strong> dengan filter yang dipilih.
                                </p>
                                <div style="display: flex; justify-content: center; gap: 10px; flex-wrap: wrap;">
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="openImportModal()">
                                        📥 Upload File Excel
                                    </button>
                                    <a href="{{ route('superadmin.students.create', ['subject_id' => $currentSubject->id]) }}" class="btn btn-primary btn-sm">
                                        + Tambah Siswa Manual
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($students->hasPages())
        <div class="card-footer" style="padding: 1rem 1.5rem; background: #ffffff; border-top: 1px solid var(--border-color);">
            {{ $students->links() }}
        </div>
    @endif
</div>

<!-- Modal 1: Import Excel -->
<div id="importExcelModal" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #ffffff; border-radius: var(--radius-lg); max-width: 600px; width: 100%; box-shadow: 0 20px 40px rgba(0,0,0,0.25); overflow: hidden; animation: modalFadeIn 0.2s ease;">
        <div style="padding: 1.25rem 1.5rem; background: #f8fafc; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 1.4rem;">📥</span>
                <h3 style="margin: 0; font-size: 1.2rem; font-weight: 800; color: #0f172a;">
                    Import Data Siswa via Excel
                </h3>
            </div>
            <button type="button" onclick="closeImportModal()" style="background: none; border: none; font-size: 1.5rem; color: #64748b; cursor: pointer; line-height: 1;">&times;</button>
        </div>

        <form action="{{ route('superadmin.students.import-excel') }}" method="POST" enctype="multipart/form-data" style="padding: 1.5rem;">
            @csrf

            <div class="form-group">
                <label class="form-label" for="modal_subject_id" style="font-weight: 700;">
                    Mata Pelajaran Tujuan *
                </label>
                <select name="subject_id" id="modal_subject_id" class="form-control" required style="font-weight: 700;">
                    @foreach($subjects as $subj)
                        <option value="{{ $subj->id }}" {{ $currentSubject->id === $subj->id ? 'selected' : '' }}>
                            {{ $subj->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" id="importTeacherGroup" style="{{ (int)$currentSubject->id === 1 ? '' : 'display: none;' }}">
                <label class="form-label" for="import_teacher_id" style="font-weight: 700; color: #1e3a8a;">
                    Pilih Guru Pengajar (Khusus Bahasa Inggris)
                </label>
                <select name="teacher_id" id="import_teacher_id" class="form-control" style="font-weight: 600; background: #ffffff; border-color: #93c5fd;">
                    <option value="">-- Pilih Guru Pengajar --</option>
                    @foreach($englishTeachers as $t)
                        <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->email }})</option>
                    @endforeach
                </select>
                <small style="color: var(--text-muted); font-size: 0.78rem; display: block; margin-top: 4px;">
                    Siswa pada berkas Excel ini akan langsung terhubung ke guru pengajar yang dipilih. Murid tambahan baru nantinya bisa diajar oleh guru yang berbeda.
                </small>
            </div>

            <div class="form-group">
                <label class="form-label" for="excel_file" style="font-weight: 700;">
                    Pilih File Excel / CSV (.xlsx, .csv, .xls) *
                </label>
                <input type="file" name="excel_file" id="excel_file" class="form-control" accept=".xlsx,.csv,.xls" required>
                <small style="color: var(--text-muted); font-size: 0.78rem; display: block; margin-top: 4px;">
                    Format file yang didukung: <strong>.xlsx</strong> (Microsoft Excel) atau <strong>.csv</strong>. Maksimal 20 MB.
                </small>
            </div>

            <div style="background: #f1f5f9; border-left: 4px solid var(--primary); padding: 12px 14px; border-radius: 4px; margin-bottom: 1.25rem;">
                <strong style="color: #0f172a; font-size: 0.85rem; display: block; margin-bottom: 4px;">
                    📋 Panduan Format Kolom File Excel:
                </strong>
                <p style="margin: 0; font-size: 0.78rem; color: #475569; line-height: 1.4;">
                    File dapat memiliki header kolom berikut (urutan fleksibel):<br>
                    • <strong>KELAS</strong> : Contoh: <code>I1</code>, <code>Grup 1</code>, <code>Kelas Matematika Dasar</code><br>
                    • <strong>NRP</strong> : Nomor Induk Siswa<br>
                    • <strong>NAMA PESERTA</strong> : Nama lengkap siswa<br>
                    • <strong>BAGIAN</strong> : Divisi / Departemen (opsional)<br>
                    • <strong>EMAIL</strong> : Opsional (jika kosong otomatis: <code>nrp@musashi.id</code>)<br>
                    • <strong>PASSWORD</strong> : Opsional (default: <code>[NRP]@musashi</code>)
                </p>
                <div style="margin-top: 8px;">
                    <a href="{{ route('superadmin.students.download-template', ['subject_id' => $currentSubject->id]) }}" style="font-size: 0.78rem; font-weight: 700; color: var(--primary); text-decoration: underline;">
                        ⬇️ Unduh Contoh Template Excel (.csv)
                    </a>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 1rem;">
                <button type="button" class="btn btn-secondary" onclick="closeImportModal()">Batal</button>
                <button type="submit" class="btn btn-primary" style="font-weight: 700; padding: 10px 20px;">
                    🚀 Mulai Import Siswa
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Hapus Semua Siswa -->
<div id="deleteAllModal" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.65); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #ffffff; border-radius: var(--radius-lg); max-width: 520px; width: 100%; box-shadow: 0 25px 50px -12px rgba(220,38,38,0.35); overflow: hidden; animation: modalFadeIn 0.2s ease;">
        <div style="padding: 1.25rem 1.5rem; background: #fef2f2; border-bottom: 1px solid #fee2e2; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 1.4rem;">⚠️</span>
                <h3 style="margin: 0; font-size: 1.2rem; font-weight: 800; color: #991b1b;">
                    Hapus Semua Data Siswa
                </h3>
            </div>
            <button type="button" onclick="closeDeleteAllModal()" style="background: none; border: none; font-size: 1.5rem; color: #991b1b; cursor: pointer; line-height: 1;">&times;</button>
        </div>

        <form action="{{ route('superadmin.students.delete-all') }}" method="POST" style="padding: 1.5rem;">
            @csrf
            @method('DELETE')

            <p style="color: #475569; font-size: 0.92rem; line-height: 1.5; margin-bottom: 1.25rem;">
                Tindakan ini akan <strong>menghapus permanen</strong> akun siswa terpilih beserta seluruh data riwayat ujian, absensi, progres belajar, dan nilai yang berkaitan. Tindakan ini tidak dapat dibatalkan.
            </p>

            <div class="form-group">
                <label class="form-label" for="delete_subject_id" style="font-weight: 700;">
                    Pilih Lingkup Siswa yang Ingin Dihapus *
                </label>
                <select name="subject_id" id="delete_subject_id" class="form-control" required style="font-weight: 700;">
                    <option value="{{ $currentSubject->id }}" selected>
                        Khusus Siswa {{ $currentSubject->name }} Saja ({{ $subjectCounts[$currentSubject->id] ?? 0 }} Siswa)
                    </option>
                    <option value="all">
                        ⚠️ Seluruh Siswa di Semua Mata Pelajaran (Inggris, Jepang & MTK)
                    </option>
                </select>
            </div>

            <div class="form-group" style="background: #fff1f2; border: 1px solid #fecdd3; border-radius: var(--radius-md); padding: 12px 14px;">
                <label class="form-label" for="confirm_text" style="font-weight: 800; color: #9f1239; margin-bottom: 6px;">
                    Konfirmasi Keamanan:
                </label>
                <p style="font-size: 0.8rem; color: #881337; margin-bottom: 8px;">
                    Untuk mencegah penghapusan yang tidak disengaja, ketik kata <strong>HAPUS</strong> (huruf kapital) di bawah ini:
                </p>
                <input type="text" name="confirm_text" id="confirm_text" class="form-control" required placeholder="Ketik HAPUS di sini..." style="border-color: #fb7185; font-weight: 800; letter-spacing: 1px;">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 1.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeDeleteAllModal()">Batal</button>
                <button type="submit" class="btn btn-danger" style="font-weight: 800; padding: 10px 22px;">
                    🗑️ Ya, Hapus Semua Siswa
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openImportModal() {
        var modal = document.getElementById('importExcelModal');
        if (modal) modal.style.display = 'flex';
    }
    function closeImportModal() {
        var modal = document.getElementById('importExcelModal');
        if (modal) modal.style.display = 'none';
    }

    function openDeleteAllModal() {
        var modal = document.getElementById('deleteAllModal');
        if (modal) modal.style.display = 'flex';
    }
    function closeDeleteAllModal() {
        var modal = document.getElementById('deleteAllModal');
        if (modal) modal.style.display = 'none';
    }

    window.addEventListener('click', function(e) {
        var m1 = document.getElementById('importExcelModal');
        var m2 = document.getElementById('deleteAllModal');
        if (e.target === m1) closeImportModal();
        if (e.target === m2) closeDeleteAllModal();
    });

    document.addEventListener('DOMContentLoaded', function() {
        var subjSelect = document.getElementById('modal_subject_id');
        var teacherGroup = document.getElementById('importTeacherGroup');
        if (subjSelect && teacherGroup) {
            subjSelect.addEventListener('change', function() {
                teacherGroup.style.display = (this.value == '1') ? 'block' : 'none';
            });
        }
    });
</script>
@endsection
