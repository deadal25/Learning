@extends('layouts.app')

@section('title', 'Kelola Materi Pembelajaran - Admin Musashi')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.8rem; margin-bottom: 0.25rem; font-weight: 800;">
            Kelola Materi Pembelajaran {{ $selectedClass ? '• ' . $selectedClass : '' }}
        </h1>
        <p style="color: var(--text-muted); font-size: 0.92rem;">
            @if(auth()->user()->subject_id == 2)
                Upload dan atur materi slide PPT & PDF per grup Bahasa Jepang agar siswa hanya dapat mengakses materi sesuai grup pilihannya.
            @elseif(auth()->user()->subject_id == 3)
                Upload dan atur materi slide PPT & PDF per kelas Matematika agar siswa hanya dapat mengakses materi sesuai kelas pilihannya.
            @else
                Upload dan atur materi slide PPT & PDF per kelas Bahasa Inggris agar siswa hanya dapat mengakses materi sesuai kelas pilihannya.
            @endif
        </p>
    </div>
    <a href="{{ route('admin.materials.create') }}" class="btn btn-primary" style="font-weight: 700;">
        + Upload Materi Baru
    </a>
</div>

<!-- Filter Bar -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body" style="padding: 1rem 1.5rem;">
        <form method="GET" action="{{ route('admin.materials.index') }}" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <div style="min-width: 220px;">
                <select name="class_name" class="form-control" onchange="this.form.submit()" style="font-weight: 600;">
                    <option value="all">-- Semua Kelas / Grup --</option>
                    @php
                        $classesI = isset($classes) ? $classes->filter(fn($c) => str_starts_with(strtoupper($c->name), 'I')) : collect();
                        $classesB = isset($classes) ? $classes->filter(fn($c) => str_starts_with(strtoupper($c->name), 'B')) : collect();
                        $classesE = isset($classes) ? $classes->filter(fn($c) => str_starts_with(strtoupper($c->name), 'E')) : collect();
                        $hasLetterGroups = ($classesI->isNotEmpty() || $classesB->isNotEmpty() || $classesE->isNotEmpty());
                    @endphp
                    @if($hasLetterGroups)
                        <optgroup label="── GRUP GABUNGAN HURUF ──">
                            <option value="I" {{ ($selectedClass ?? '') === 'I' ? 'selected' : '' }}>📚 Seluruh Kelas I (I1 - I6)</option>
                            <option value="B" {{ ($selectedClass ?? '') === 'B' ? 'selected' : '' }}>📚 Seluruh Kelas B (B1 - B8)</option>
                            <option value="E" {{ ($selectedClass ?? '') === 'E' ? 'selected' : '' }}>📚 Seluruh Kelas E (E1)</option>
                        </optgroup>
                        <optgroup label="── KELAS SPESIFIK ──">
                            @foreach($classes as $c)
                                <option value="{{ $c->name }}" {{ ($selectedClass ?? '') === $c->name ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </optgroup>
                    @else
                        @if(isset($classes))
                            @foreach($classes as $c)
                                <option value="{{ $c->name }}" {{ ($selectedClass ?? '') === $c->name ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        @endif
                    @endif
                </select>
            </div>

            <div style="flex: 1; min-width: 220px;">
                <input type="text" name="search" class="form-control" placeholder="Cari judul materi, nama file, atau kelas..." value="{{ request('search') }}">
            </div>

            @if($isTeacher)
                <div>
                    <select name="level_id" class="form-control" onchange="this.form.submit()" style="font-weight: 600;">
                        <option value="">-- Semua Pertemuan (1 - {{ $teacherLevels->count() ?: 25 }}) --</option>
                        @foreach($teacherLevels as $tl)
                            <option value="{{ $tl->id }}" {{ request('level_id') == $tl->id ? 'selected' : '' }}>
                                🗓️ {{ $tl->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @else
                <div>
                    <select name="subject_id" class="form-control" onchange="this.form.submit()">
                        <option value="">Semua Mapel</option>
                        @foreach($subjects as $subj)
                            <option value="{{ $subj->id }}" {{ request('subject_id') == $subj->id ? 'selected' : '' }}>
                                {{ $subj->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div style="min-width: 175px;">
                <select name="status" class="form-control" onchange="this.form.submit()" style="font-weight: 600;">
                    <option value="">-- Semua Status --</option>
                    <option value="active" {{ ($selectedStatus ?? request('status')) === 'active' ? 'selected' : '' }}>🟢 Aktif (Terlihat Siswa)</option>
                    <option value="inactive" {{ ($selectedStatus ?? request('status')) === 'inactive' ? 'selected' : '' }}>🔴 Nonaktif (Disembunyikan)</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary" style="font-weight: 700;">Filter Materi</button>
            @if(request()->hasAny(['search', 'subject_id', 'level_id', 'class_name', 'status']))
                <a href="{{ route('admin.materials.index') }}" class="btn btn-secondary" style="color: var(--text-muted);">Reset</a>
            @endif
        </form>
    </div>
</div>

<div class="card" style="box-shadow: var(--shadow-sm);">
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table" style="vertical-align: middle;">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">No</th>
                        <th>Judul Materi</th>
                        <th>Khusus Kelas</th>
                        <th>Pertemuan</th>
                        <th style="text-align: center; width: 160px;">Status Akses Siswa</th>
                        <th>Format File</th>
                        <th>Tanggal Unggah</th>
                        <th style="text-align: right; width: 220px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($materials as $index => $mat)
                        <tr style="{{ !$mat->is_active ? 'background-color: #fafaf9;' : '' }}">
                            <td style="text-align: center; font-weight: 600; color: #64748b;">
                                {{ $materials->firstItem() + $index }}
                            </td>
                            <td>
                                <div style="display: flex; align-items: flex-start; gap: 10px;">
                                    <div style="font-size: 1.5rem;">
                                        @if($mat->isPpt())
                                            📊
                                        @elseif($mat->isPdf())
                                            📕
                                        @else
                                            🌐
                                        @endif
                                    </div>
                                    <div>
                                        <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                            <strong style="color: #0f172a; font-size: 0.95rem;">{{ $mat->title }}</strong>
                                            @if(!$mat->is_active)
                                                <span class="badge" style="background: #fee2e2; color: #991b1b; font-size: 0.72rem; padding: 2px 7px; font-weight: 700;">Nonaktif / Tersembunyi</span>
                                            @endif
                                        </div>
                                        @if($mat->description)
                                            <div style="font-size: 0.78rem; color: var(--text-muted);">{{ Str::limit($mat->description, 60) }}</div>
                                        @endif
                                        @if($mat->file_name)
                                            <div style="font-size: 0.74rem; color: #475569; font-family: monospace;">📁 {{ $mat->file_name }}</div>
                                        @endif
                                        @if($mat->teacher)
                                            <div style="font-size: 0.74rem; color: #4f46e5; font-weight: 600; margin-top: 2px;">
                                                👤 Pengunggah: {{ $mat->teacher_id === auth()->id() ? 'Anda (' . $mat->teacher->name . ')' : $mat->teacher->name }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($mat->class_name)
                                    @php
                                        $cUpper = strtoupper(trim($mat->class_name));
                                    @endphp
                                    @if($cUpper === 'I')
                                        <span class="badge" style="font-size: 0.82rem; font-weight: 700; padding: 4px 10px; background: #e0e7ff; color: #3730a3; border: 1px solid #c7d2fe;">
                                            📚 Seluruh Kelas I (I1 - I6)
                                        </span>
                                    @elseif($cUpper === 'B')
                                        <span class="badge" style="font-size: 0.82rem; font-weight: 700; padding: 4px 10px; background: #f3e8ff; color: #6b21a8; border: 1px solid #e9d5ff;">
                                            📚 Seluruh Kelas B (B1 - B8)
                                        </span>
                                    @elseif($cUpper === 'E')
                                        <span class="badge" style="font-size: 0.82rem; font-weight: 700; padding: 4px 10px; background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;">
                                            📚 Seluruh Kelas E (E1)
                                        </span>
                                    @else
                                        <span class="badge badge-primary" style="font-size: 0.82rem; font-weight: 700; padding: 4px 10px;">
                                            🎓 {{ $mat->class_name }}
                                        </span>
                                    @endif
                                @else
                                    <span class="badge" style="background: #f1f5f9; color: #475569; font-size: 0.78rem;">
                                        🌐 Semua Kelas
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="badge" style="background: #e0e7ff; color: #3730a3; font-weight: 800; font-size: 0.85rem; padding: 4px 10px;">
                                    🗓️ {{ $mat->level->name }}
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <form action="{{ route('admin.materials.toggle-active', $mat) }}" method="POST" style="margin: 0; display: inline-block;">
                                    @csrf
                                    @if($mat->is_active)
                                        <button type="submit" class="btn btn-sm" style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; font-weight: 700; border-radius: 9999px; padding: 4px 12px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; transition: all 0.2s;" title="Klik untuk mematikan / menyembunyikan materi dari siswa">
                                            <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #10b981; box-shadow: 0 0 0 2px #d1fae5;"></span>
                                            <span>Aktif</span>
                                        </button>
                                    @else
                                        <button type="submit" class="btn btn-sm" style="background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; font-weight: 700; border-radius: 9999px; padding: 4px 12px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; transition: all 0.2s;" title="Klik untuk mengaktifkan materi agar siswa dapat melihat dan mengaksesnya">
                                            <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #ef4444; box-shadow: 0 0 0 2px #fee2e2;"></span>
                                            <span>Nonaktif</span>
                                        </button>
                                    @endif
                                </form>
                            </td>
                            <td>
                                @if($mat->isPpt())
                                    <span class="badge" style="background: #fef3c7; color: #92400e; font-weight: 800; border: 1px solid #fde68a; font-size: 0.78rem;">
                                        📊 Slide PPT
                                    </span>
                                @elseif($mat->isPdf())
                                    <span class="badge" style="background: #fee2e2; color: #991b1b; font-weight: 800; border: 1px solid #fecaca; font-size: 0.78rem;">
                                        📕 Dokumen PDF
                                    </span>
                                @elseif($mat->isImage())
                                    <span class="badge" style="background: #e0f2fe; color: #0369a1; font-weight: 800; font-size: 0.78rem;">
                                        🖼️ Gambar
                                    </span>
                                @else
                                    <span class="badge badge-neutral" style="font-weight: 700;">
                                        {{ strtoupper($mat->file_type ?? 'Embed URL') }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span style="font-size: 0.85rem; color: #64748b;">
                                    {{ $mat->created_at->format('d M Y') }}
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 6px;">
                                    <a href="{{ route('admin.materials.show', $mat) }}" class="btn btn-primary btn-sm" title="Buka & Tinjau Slide PPT">
                                        🖥️ Buka
                                    </a>
                                    @if($mat->file_path)
                                        <a href="{{ route('admin.materials.download', $mat) }}" class="btn btn-secondary btn-sm" title="Unduh File">
                                            Unduh
                                        </a>
                                    @endif
                                    <a href="{{ route('admin.materials.edit', $mat) }}" class="btn btn-secondary btn-sm">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.materials.destroy', $mat) }}" method="POST" onsubmit="return confirm('Hapus materi slide ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                                Belum ada materi terunggah untuk filter ini. Klik "+ Upload Materi Baru" untuk menambahkan modul materi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($materials->hasPages())
        <div class="card-footer" style="padding: 1rem 1.5rem; background: #ffffff;">
            {{ $materials->links() }}
        </div>
    @endif
</div>
@endsection
