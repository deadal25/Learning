@extends('layouts.app')

@section('title', 'Kelola Siswa - Musashi Learning')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
            @if($isTeacher && $user->subject_id == 2)
                <span class="badge" style="background: #fdf2f8; color: #db2777; font-weight: 800;">
                    Guru Bahasa Jepang
                </span>
                <span class="badge badge-primary">Grup Belajar</span>
            @elseif($isTeacher && $user->subject_id == 3)
                <span class="badge" style="background: #ecfdf5; color: #047857; font-weight: 800;">
                    Guru Matematika
                </span>
            @elseif($isTeacher && $user->subject_id == 1)
                <span class="badge" style="background: #eff6ff; color: #2563eb; font-weight: 800;">
                    Guru Bahasa Inggris
                </span>
            @else
                <span class="badge badge-neutral" style="font-weight: 800;">
                    🏫 Learning Musashi Administrator
                </span>
            @endif
        </div>
        <h1 style="font-size: 1.8rem; margin: 0; font-weight: 800; color: #0f172a;">
            @if($isTeacher && $user->subject_id == 2)
                Kelola Siswa Grup Bahasa Jepang {{ $selectedClass ? '• ' . $selectedClass : '' }}
            @elseif($isTeacher && $user->subject_id == 3)
                Kelola Siswa Kelas Matematika {{ $selectedClass ? '• ' . $selectedClass : '' }}
            @elseif($isTeacher && $user->subject_id == 1)
                Kelola Siswa Kelas Bahasa Inggris {{ $selectedClass ? '• ' . $selectedClass : '' }}
            @else
                Kelola Seluruh Siswa {{ $selectedClass ? '• ' . $selectedClass : '' }}
            @endif
        </h1>
        <p style="color: var(--text-muted); font-size: 0.92rem; margin-top: 4px;">
            @if($isTeacher && $user->subject_id == 2)
                Pantau dan kelola data siswa khusus grup Bahasa Jepang, divisi kerja, dan status aktif belajar.
            @elseif($isTeacher && $user->subject_id == 3)
                Pantau dan kelola data siswa khusus kelas Matematika, divisi kerja, dan status aktif belajar.
            @elseif($isTeacher && $user->subject_id == 1)
                Pantau dan kelola data siswa khusus kelas Bahasa Inggris, divisi kerja, dan kelas yang diikuti.
            @else
                Lihat daftar seluruh siswa terdaftar, divisi kerja, dan kelas/grup yang diikuti sesuai mata pelajaran masing-masing.
            @endif
        </p>
    </div>
    @php
        $createRouteParams = [];
        if (!$isTeacher && !empty($selectedSubjectId) && $selectedSubjectId !== 'all') {
            $createRouteParams['subject_id'] = $selectedSubjectId;
        }
    @endphp
    <div style="display: flex; gap: 8px;">
        <a href="{{ route('admin.students.create', $createRouteParams) }}" class="btn btn-primary" style="font-weight: 700; background: {{ $isTeacher && $user->subject_id == 2 ? '#db2777' : '#2563eb' }}; border-color: {{ $isTeacher && $user->subject_id == 2 ? '#db2777' : '#2563eb' }};">
            + Tambah Siswa Baru
        </a>
    </div>
</div>

<!-- Super Admin Subject Filter Tabs -->
@if(!$isTeacher && isset($subjects))
    <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 10px 1.25rem; margin-bottom: 1.25rem; display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
        <span style="font-size: 0.82rem; font-weight: 700; color: #64748b; margin-right: 6px;">Filter Mapel Siswa:</span>
        <a href="{{ route('admin.students.index', ['subject_id' => 'all']) }}" class="btn btn-sm {{ (empty($selectedSubjectId) || $selectedSubjectId === 'all') ? 'btn-primary' : 'btn-secondary' }}" style="font-weight: 700; border-radius: 20px; font-size: 0.8rem; padding: 4px 14px;">
            Semua Siswa
        </a>
        @foreach($subjects as $subj)
            <a href="{{ route('admin.students.index', ['subject_id' => $subj->id]) }}" class="btn btn-sm {{ ($selectedSubjectId == $subj->id) ? 'btn-primary' : 'btn-secondary' }}" style="font-weight: 700; border-radius: 20px; font-size: 0.8rem; padding: 4px 14px;">
                Siswa {{ $subj->name }}
            </a>
        @endforeach
    </div>
@endif

<!-- Search & Class Filter -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body" style="padding: 1rem 1.5rem;">
        <form method="GET" action="{{ route('admin.students.index') }}" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            @if(!$isTeacher && $selectedSubjectId)
                <input type="hidden" name="subject_id" value="{{ $selectedSubjectId }}">
            @endif

            <div style="min-width: 200px;">
                <select name="class_name" class="form-control" onchange="this.form.submit()" style="font-weight: 600;">
                    <option value="all">
                        @if(($isTeacher && $user->subject_id == 2) || (isset($selectedSubjectId) && $selectedSubjectId == 2))
                            -- Semua Grup Jepang --
                        @elseif(($isTeacher && $user->subject_id == 1) || (isset($selectedSubjectId) && $selectedSubjectId == 1))
                            -- Semua Kelas Inggris --
                        @else
                            -- Semua Kelas / Grup Belajar --
                        @endif
                    </option>
                    @if(isset($classes))
                        @foreach($classes as $c)
                            <option value="{{ $c->name }}" {{ ($selectedClass ?? '') === $c->name ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>

            <div style="flex: 1; min-width: 220px;">
                <input type="text" name="search" class="form-control" placeholder="Cari nama, email, divisi, atau kelas..." value="{{ request('search') }}">
            </div>

            <div>
                <select name="status" class="form-control">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary" style="font-weight: 700;">Filter Siswa</button>
            @if(request()->hasAny(['search', 'status', 'class_name', 'subject_id']))
                <a href="{{ route('admin.students.index') }}" class="btn btn-secondary" style="color: var(--text-muted);">Reset</a>
            @endif
        </form>
    </div>
</div>

<!-- Table Card -->
<div class="card" style="box-shadow: var(--shadow-sm);">
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table" style="vertical-align: middle;">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">No</th>
                        <th>Nama Siswa & NRP</th>
                        <th>Email Login</th>
                        <th>Kata Sandi</th>
                        <th>Divisi Kerja</th>
                        <th>{{ (($isTeacher && $user->subject_id == 2) || (isset($selectedSubjectId) && $selectedSubjectId == 2)) ? 'Grup Terdaftar' : 'Kelas Terdaftar' }}</th>
                        <th>No. WhatsApp / HP</th>
                        <th>Status</th>
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
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div class="avatar" style="width: 36px; height: 36px; font-size: 0.9rem; background: linear-gradient(135deg, #2563eb, #1d4ed8); font-weight: 800; color: #fff;">
                                        {{ strtoupper(substr($student->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <strong style="color: #0f172a; font-size: 0.95rem;">{{ $student->name }}</strong>
                                        @if($student->nrp)
                                            <div style="font-size: 0.74rem; color: #475569; font-weight: 700; font-family: monospace; margin-top: 2px;">
                                                <span style="background: #e2e8f0; color: #1e293b; padding: 1px 6px; border-radius: 4px;">NRP: {{ $student->nrp }}</span>
                                            </div>
                                        @else
                                            <div style="font-size: 0.76rem; color: var(--text-muted);">ID: #{{ $student->id }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span style="font-size: 0.88rem; color: #334155; font-family: monospace;">{{ $student->email }}</span>
                            </td>
                            <td>
                                <div style="display: inline-flex; align-items: center; gap: 4px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 2px 7px;">
                                    <span style="font-size: 0.8rem;">🔑</span>
                                    <span style="font-family: monospace; font-weight: 700; color: #166534; font-size: 0.82rem;">{{ $studentPwd }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge" style="background: #f1f5f9; color: #334155; font-size: 0.82rem; font-weight: 600; padding: 4px 8px;">
                                    🏢 {{ $student->division ?: 'Umum / Belum diisi' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-primary" style="font-size: 0.82rem; font-weight: 700; padding: 4px 10px;">
                                    🎓 {{ $student->class_name ?: 'Belum Ada' }}
                                </span>
                            </td>
                            <td>
                                <span style="font-size: 0.85rem; color: #475569;">{{ $student->phone ?? '-' }}</span>
                            </td>
                            <td>
                                @if($student->status === 'active')
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-danger">Non-Aktif</span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                    <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-secondary btn-sm" title="Edit Siswa & Kelas">
                                        ✏ Edit
                                    </a>
                                    <form action="{{ route('admin.students.destroy', $student) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun siswa ini?')" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus Akun">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                                Tidak ada data siswa yang cocok dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($students->hasPages())
        <div class="card-footer" style="padding: 1rem 1.5rem; background: #ffffff;">
            {{ $students->links() }}
        </div>
    @endif
</div>
@endsection
