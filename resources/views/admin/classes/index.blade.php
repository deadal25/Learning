@extends('layouts.app')

@section('title', 'Kelola Kelas & Profil - Admin Musashi')

@section('content')
@php
    $isJapanese = ($isTeacher && $user->subject_id == 2) || (!$isTeacher && $selectedSubjectId == 2);
@endphp
<div style="margin-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                @if($isTeacher && $user->subject_id == 2)
                    <span class="badge" style="background: #fdf2f8; color: #db2777; font-weight: 800;">
                        Pengaturan Grup Bahasa Jepang
                    </span>
                    <span class="badge badge-primary">Grup Belajar</span>
                @elseif($isTeacher && $user->subject_id == 3)
                    <span class="badge" style="background: #ecfdf5; color: #047857; font-weight: 800;">
                        Pengaturan Kelas Matematika
                    </span>
                @elseif($isTeacher && $user->subject_id == 1)
                    <span class="badge" style="background: #eff6ff; color: #2563eb; font-weight: 800;">
                        Pengaturan Kelas Bahasa Inggris
                    </span>
                @else
                    <span class="badge badge-neutral" style="font-weight: 800;">
                        🏫 Kelola Kelas & Grup Mata Pelajaran
                    </span>
                @endif
            </div>
            <h1 style="font-size: 1.85rem; margin: 0; font-weight: 800; color: #0f172a;">
                @if($isTeacher && $user->subject_id == 2)
                    Kelola Nama Grup Bahasa Jepang
                @elseif($isTeacher && $user->subject_id == 3)
                    Kelola Nama Kelas Matematika
                @elseif($isTeacher && $user->subject_id == 1)
                    Kelola Nama Kelas Bahasa Inggris
                @else
                    Kelola Nama Kelas & Grup Mata Pelajaran
                @endif
            </h1>
            <p style="color: var(--text-muted); font-size: 0.92rem; margin-top: 4px;">
                @if($isTeacher && $user->subject_id == 2)
                    Atur nama-nama grup belajar Bahasa Jepang. Anda dapat menambah grup baru, mengedit nama grup, atau menghapus grup sesuai kebutuhan.
                @elseif($isTeacher && $user->subject_id == 3)
                    Atur nama-nama kelas belajar Matematika. Anda dapat menambah kelas baru, mengedit, atau menghapus kelas sesuai kebutuhan.
                @elseif($isTeacher && $user->subject_id == 1)
                    Atur nama-nama kelas belajar Bahasa Inggris. Anda dapat menambah kelas baru, mengedit, atau menghapus kelas sesuai kebutuhan.
                @else
                    Atur nama-nama kelas Bahasa Inggris, grup Bahasa Jepang, serta Matematika secara terpisah dan terstruktur.
                @endif
            </p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary btn-sm">
            &larr; Dashboard
        </a>
    </div>
</div>

<div class="layout-sidebar-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; align-items: flex-start;">
    <!-- Left Column: Kelola Nama-Nama Kelas -->
    <div>
        <div class="card" style="box-shadow: var(--shadow-sm); border-radius: var(--radius-lg); overflow: hidden; margin-bottom: 2rem;">
            <div class="card-header" style="background: #ffffff; padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div>
                    <h2 style="font-size: 1.25rem; margin: 0; font-weight: 800; color: #0f172a;">
                        🏫 Daftar Nama {{ $isJapanese ? 'Grup' : 'Kelas' }} {{ $isTeacher && $user->subject ? '• ' . $user->subject->name : '' }}
                    </h2>
                    <span style="font-size: 0.82rem; color: var(--text-muted);">
                        Total {{ $classes->count() }} {{ $isJapanese ? 'grup' : 'kelas' }} terdaftar dalam sistem
                    </span>
                </div>
                <button type="button" class="btn btn-primary btn-sm" onclick="openAddClassModal()" style="font-weight: 700; background: {{ $isTeacher && $user->subject_id == 2 ? '#db2777' : '#2563eb' }}; border-color: {{ $isTeacher && $user->subject_id == 2 ? '#db2777' : '#2563eb' }};">
                    + Tambah {{ $isJapanese ? 'Grup' : 'Kelas' }} Baru
                </button>
            </div>

            <!-- Subject Filter Pills (Super Admin / Admin) -->
            @if(isset($subjects) && $subjects->count() > 1 && !$isTeacher)
                <div style="background: #f8fafc; border-bottom: 1px solid var(--border-color); padding: 10px 1.5rem; display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
                    <span style="font-size: 0.8rem; font-weight: 700; color: #64748b; margin-right: 4px;">Filter Mapel:</span>
                    @foreach($subjects as $subj)
                        <a href="{{ route('admin.classes.index', ['subject_id' => $subj->id]) }}" class="btn btn-sm {{ ($selectedSubjectId == $subj->id) ? 'btn-primary' : 'btn-secondary' }}" style="font-weight: 700; border-radius: 20px; font-size: 0.8rem; padding: 4px 14px;">
                            {{ $subj->name }}
                        </a>
                    @endforeach
                    <a href="{{ route('admin.classes.index', ['subject_id' => 'all']) }}" class="btn btn-sm {{ ($selectedSubjectId === 'all') ? 'btn-primary' : 'btn-secondary' }}" style="font-weight: 700; border-radius: 20px; font-size: 0.8rem; padding: 4px 14px;">
                        Semua Mapel
                    </a>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table" style="vertical-align: middle;">
                    <thead>
                        <tr style="background: #f8fafc; font-size: 0.84rem;">
                            <th style="width: 50px; text-align: center;">No</th>
                            <th>Mata Pelajaran</th>
                            <th>Nama {{ $isJapanese ? 'Grup' : 'Kelas' }}</th>
                            <th>{{ $isJapanese ? 'Kategori Grup' : 'Tingkatan Level' }}</th>
                            <th>Deskripsi / Keterangan</th>
                            <th style="text-align: center;">Siswa</th>
                            <th style="text-align: center;">Materi</th>
                            <th style="text-align: right; width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($classes as $idx => $cls)
                            <tr>
                                <td style="text-align: center; font-weight: 700; color: #64748b;">
                                    {{ $idx + 1 }}
                                </td>
                                <td>
                                    <span class="badge" style="background: {{ $cls->subject_id == 2 ? '#fdf2f8' : ($cls->subject_id == 3 ? '#ecfdf5' : '#eff6ff') }}; color: {{ $cls->subject_id == 2 ? '#be185d' : ($cls->subject_id == 3 ? '#047857' : '#1d4ed8') }}; font-weight: 800; font-size: 0.76rem;">
                                        {{ $cls->subject?->name ?? 'Bahasa Inggris' }}
                                    </span>
                                </td>
                                <td>
                                    <strong style="font-size: 0.98rem; color: #0f172a;">{{ $cls->name }}</strong>
                                </td>
                                <td>
                                    @if($cls->subject_id == 2)
                                        <span class="badge" style="background: #fdf2f8; color: #db2777; font-size: 0.78rem; font-weight: 700;">
                                            Grup Belajar
                                        </span>
                                    @elseif(strtolower($cls->level_name) === 'beginner')
                                        <span class="badge badge-success" style="font-size: 0.78rem;">
                                            🌱 {{ $cls->level_name }}
                                        </span>
                                    @elseif(str_contains(strtolower($cls->level_name), 'dasar'))
                                        <span class="badge" style="background: #ecfdf5; color: #059669; font-size: 0.78rem; font-weight: 700;">
                                            {{ $cls->level_name }}
                                        </span>
                                    @else
                                        <span class="badge badge-primary" style="font-size: 0.78rem;">
                                            {{ $cls->level_name }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span style="font-size: 0.82rem; color: #475569;">
                                        {{ $cls->description ?: '—' }}
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge" style="background: #f1f5f9; color: #1e293b; font-weight: 700;">
                                        {{ $cls->students_count }} Siswa
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge" style="background: #eff6ff; color: #1e40af; font-weight: 700;">
                                        {{ $cls->materials_count }} Modul
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                        <button type="button" class="btn btn-secondary btn-sm" onclick="openEditClassModal({{ $cls->id }}, '{{ addslashes($cls->name) }}', '{{ addslashes($cls->level_name) }}', '{{ addslashes($cls->description ?? '') }}', {{ $cls->subject_id ?? 1 }})">
                                            ✏ Edit
                                        </button>
                                        <form action="{{ route('admin.classes.destroy', $cls) }}" method="POST" onsubmit="return confirm('Hapus {{ $cls->subject_id == 2 ? 'grup' : 'kelas' }} {{ $cls->name }}?')" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Hapus {{ $cls->subject_id == 2 ? 'Grup' : 'Kelas' }}">
                                                &times;
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 2.5rem; color: var(--text-muted);">
                                    Belum ada kelas terdaftar untuk mata pelajaran ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Column: Profil Pengajar -->
    <div>
        <div class="card" style="box-shadow: var(--shadow-sm); border-radius: var(--radius-lg); overflow: hidden;">
            <div class="card-header" style="background: #ffffff; padding: 1.25rem 1.5rem;">
                <h3 style="font-size: 1.15rem; margin: 0; font-weight: 800; color: #0f172a;">
                    👤 Data Profil Anda
                </h3>
                <span style="font-size: 0.8rem; color: var(--text-muted);">
                    Perbarui nama, kontak, & kredensial akun
                </span>
            </div>

            <div class="card-body" style="padding: 1.5rem;">
                @if(session('profile_success'))
                    <div class="alert alert-success" style="margin-bottom: 1.25rem; font-size: 0.88rem;">
                        {{ session('profile_success') }}
                    </div>
                @endif

                <form action="{{ route('admin.profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label class="form-label" for="prof_name" style="font-weight: 700;">Nama Lengkap Pengajar *</label>
                        <input type="text" name="name" id="prof_name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="prof_email" style="font-weight: 700;">Alamat Email Login *</label>
                        <input type="email" name="email" id="prof_email" class="form-control" value="{{ old('email', $user->email) }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="prof_phone" style="font-weight: 700;">No. WhatsApp / HP</label>
                        <input type="text" name="phone" id="prof_phone" class="form-control" value="{{ old('phone', $user->phone) }}" placeholder="08xxxxxxxxxx">
                    </div>

                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: var(--radius-md); padding: 12px; margin-top: 1.25rem;">
                        <span style="font-size: 0.78rem; font-weight: 700; color: #64748b; text-transform: uppercase; display: block; margin-bottom: 8px;">
                            Ubah Kata Sandi (Opsional)
                        </span>

                        <div class="form-group">
                            <label class="form-label" for="prof_password" style="font-size: 0.82rem; font-weight: 600;">Kata Sandi Baru</label>
                            <input type="password" name="password" id="prof_password" class="form-control" placeholder="Kosongkan jika tidak diganti">
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" for="prof_password_confirmation" style="font-size: 0.82rem; font-weight: 600;">Konfirmasi Sandi Baru</label>
                            <input type="password" name="password_confirmation" id="prof_password_confirmation" class="form-control" placeholder="Ulangi kata sandi baru">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; font-weight: 800; padding: 11px; margin-top: 1.5rem; box-shadow: 0 4px 12px rgba(37,99,235,0.25);">
                        Simpan Profil Pengajar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Edit Nama Kelas / Grup -->
<div id="editClassModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #ffffff; border-radius: var(--radius-lg); max-width: 480px; width: 100%; box-shadow: var(--shadow-xl); overflow: hidden;">
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 1.2rem; margin: 0; color: #0f172a; font-weight: 800;">Edit Data {{ $isJapanese ? 'Grup' : 'Kelas' }}</h3>
            <button type="button" onclick="closeEditClassModal()" style="background:none; border:none; font-size: 1.4rem; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>

        <form id="editClassForm" action="" method="POST">
            @csrf
            @method('PUT')
            <div style="padding: 1.5rem;">
                @if(!$isTeacher)
                    <div class="form-group">
                        <label class="form-label" for="edit_subject_id" style="font-weight: 700;">Mata Pelajaran *</label>
                        <select name="subject_id" id="edit_subject_id" class="form-control" required>
                            @foreach($subjects as $subj)
                                <option value="{{ $subj->id }}">{{ $subj->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="form-group">
                    <label class="form-label" for="edit_class_name" style="font-weight: 700;">Nama {{ $isJapanese ? 'Grup' : 'Kelas' }} *</label>
                    <input type="text" name="name" id="edit_class_name" class="form-control" required placeholder="{{ $isJapanese ? 'Contoh: Grup13' : 'Masukkan nama kelas' }}">
                    <span class="form-text">Mengubah nama akan otomatis memperbarui data siswa & materi yang terhubung.</span>
                </div>

                @if(!$isJapanese)
                    <div class="form-group">
                        <label class="form-label" for="edit_level_name" style="font-weight: 700;">Tingkatan Level</label>
                        <input type="text" name="level_name" id="edit_level_name" class="form-control" placeholder="Contoh: Beginner">
                    </div>
                @else
                    <input type="hidden" name="level_name" id="edit_level_name" value="">
                @endif

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="edit_class_desc" style="font-weight: 700;">Keterangan / Deskripsi</label>
                    <input type="text" name="description" id="edit_class_desc" class="form-control" placeholder="{{ $isJapanese ? 'Contoh: Grup Belajar Bahasa Jepang' : 'Contoh: Kelas Belajar' }}">
                </div>
            </div>

            <div style="padding: 1rem 1.5rem; background: #f8fafc; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary" onclick="closeEditClassModal()">Batal</button>
                <button type="submit" class="btn btn-primary" style="font-weight: 700; background: {{ $isJapanese ? '#db2777' : '#2563eb' }}; border-color: {{ $isJapanese ? '#db2777' : '#2563eb' }};">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Tambah Kelas / Grup Baru -->
<div id="addClassModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #ffffff; border-radius: var(--radius-lg); max-width: 480px; width: 100%; box-shadow: var(--shadow-xl); overflow: hidden;">
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 1.2rem; margin: 0; color: #0f172a; font-weight: 800;">
                @if($isTeacher && $user->subject_id == 2)
                    + Tambah Grup Bahasa Jepang Baru
                @elseif($isTeacher && $user->subject_id == 1)
                    + Tambah Kelas Bahasa Inggris Baru
                @else
                    + Tambah {{ $isJapanese ? 'Grup' : 'Kelas' }} Baru
                @endif
            </h3>
            <button type="button" onclick="closeAddClassModal()" style="background:none; border:none; font-size: 1.4rem; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>

        <form action="{{ route('admin.classes.store') }}" method="POST">
            @csrf
            <div style="padding: 1.5rem;">
                @if(!$isTeacher)
                    <div class="form-group">
                        <label class="form-label" for="add_subject_id" style="font-weight: 700;">Mata Pelajaran *</label>
                        <select name="subject_id" id="add_subject_id" class="form-control" required>
                            @foreach($subjects as $subj)
                                <option value="{{ $subj->id }}" {{ $selectedSubjectId == $subj->id ? 'selected' : '' }}>
                                    {{ $subj->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="form-group">
                    <label class="form-label" for="add_class_name" style="font-weight: 700;">
                        Nama {{ $isJapanese ? 'Grup' : 'Kelas' }} Baru *
                    </label>
                    <input type="text" name="name" id="add_class_name" class="form-control" required placeholder="{{ $isJapanese ? 'Contoh: Grup14 atau Grup Khusus' : 'Contoh: B9 atau Kelas Baru' }}">
                </div>

                @if(!$isJapanese)
                    <div class="form-group">
                        <label class="form-label" for="add_level_name" style="font-weight: 700;">
                            Tingkatan Level
                        </label>
                        <input type="text" name="level_name" id="add_level_name" class="form-control" placeholder="Contoh: Beginner / Intermediate / Dasar">
                    </div>
                @endif

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="add_class_desc" style="font-weight: 700;">Keterangan / Deskripsi</label>
                    <input type="text" name="description" id="add_class_desc" class="form-control" placeholder="{{ $isJapanese ? 'Contoh: Grup Belajar Bahasa Jepang Musashi' : 'Contoh: Kelas Belajar' }}">
                </div>
            </div>

            <div style="padding: 1rem 1.5rem; background: #f8fafc; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary" onclick="closeAddClassModal()">Batal</button>
                <button type="submit" class="btn btn-primary" style="font-weight: 700; background: {{ $isJapanese ? '#db2777' : '#2563eb' }}; border-color: {{ $isJapanese ? '#db2777' : '#2563eb' }};">
                    Tambah {{ $isJapanese ? 'Grup' : 'Kelas' }}
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openEditClassModal(id, name, level, desc, subjId) {
    document.getElementById('edit_class_name').value = name;
    document.getElementById('edit_level_name').value = level;
    document.getElementById('edit_class_desc').value = desc || '';
    const editSubj = document.getElementById('edit_subject_id');
    if (editSubj && subjId) editSubj.value = subjId;
    document.getElementById('editClassForm').action = "{{ url('admin/classes') }}/" + id;
    document.getElementById('editClassModal').style.display = 'flex';
}

function closeEditClassModal() {
    document.getElementById('editClassModal').style.display = 'none';
}

function openAddClassModal() {
    document.getElementById('addClassModal').style.display = 'flex';
}

function closeAddClassModal() {
    document.getElementById('addClassModal').style.display = 'none';
}
</script>
@endpush
@endsection
