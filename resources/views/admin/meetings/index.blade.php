@extends('layouts.app')

@section('title', 'Kelola Pertemuan Belajar - ' . $currentSubject->name . ' - Musashi')

@section('content')
<div style="margin-bottom: 2rem;">
    <!-- Subject Filter Tabs for Super Admin -->
    @if(!$isTeacher && $allSubjects->count() > 1)
        <div style="display: flex; gap: 8px; margin-bottom: 1.25rem; flex-wrap: wrap;">
            @foreach($allSubjects as $subj)
                <a href="{{ route('admin.meetings.index', ['subject_id' => $subj->id]) }}"
                   class="btn {{ $subj->id === $currentSubject->id ? 'btn-primary' : 'btn-secondary' }} btn-sm"
                   style="border-radius: 9999px; font-weight: 700; padding: 6px 16px;">
                    {{ $subj->name }} ({{ $subj->levels()->count() }} Pertemuan)
                </a>
            @endforeach
        </div>
    @endif

    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px; flex-wrap: wrap;">
                <span class="badge" style="background: {{ $currentSubject->id == 2 ? '#fdf2f8' : ($currentSubject->id == 1 ? '#eff6ff' : '#ecfdf5') }}; color: {{ $currentSubject->id == 2 ? '#be185d' : ($currentSubject->id == 1 ? '#1e40af' : '#065f46') }}; font-weight: 800; font-size: 0.8rem; border: 1px solid {{ $currentSubject->id == 2 ? '#fbcfe8' : ($currentSubject->id == 1 ? '#bfdbfe' : '#a7f3d0') }};">
                    {{ $currentSubject->name }}
                </span>
                <span class="badge" style="background: #f8fafc; color: #475569; font-weight: 700; font-size: 0.78rem; border: 1px solid #e2e8f0;">
                    Learning Musashi Pengajar
                </span>
            </div>
            <h1 style="font-size: 1.85rem; margin: 0; font-weight: 800; color: #0f172a;">
                🗓️ Kelola Pertemuan Belajar
            </h1>
            <p style="color: var(--text-muted); font-size: 0.92rem; margin-top: 4px;">
                Atur daftar pertemuan belajar, tambah pertemuan baru, atau hapus pertemuan untuk kurikulum <strong>{{ $currentSubject->name }}</strong>.
            </p>
        </div>

        <div style="display: flex; gap: 10px; align-items: center;">
            <button type="button" class="btn btn-primary" onclick="openCreateMeetingModal()" style="display: flex; align-items: center; gap: 6px; font-weight: 700; box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);">
                <span style="font-size: 1.1rem; line-height: 1;">➕</span>
                <span>Tambah Pertemuan Baru</span>
            </button>
            <a href="{{ route('admin.materials.index') }}" class="btn btn-secondary btn-sm" style="font-weight: 600;">
                📖 Modul Materi &rarr;
            </a>
        </div>
    </div>
</div>

<!-- Overview Stats Cards -->
<div class="grid grid-cols-3" style="gap: 1.25rem; margin-bottom: 2rem;">
    <!-- Stat 1: Total Meetings -->
    <div class="card" style="border-left: 4px solid #2563eb; box-shadow: var(--shadow-sm); border-radius: var(--radius-md);">
        <div class="card-body" style="padding: 1.25rem;">
            <div style="font-size: 0.76rem; font-weight: 700; color: #1e40af; text-transform: uppercase; letter-spacing: 0.5px;">
                Total Pertemuan Terdaftar
            </div>
            <div style="font-size: 1.85rem; font-weight: 800; color: #2563eb; margin-top: 4px;">
                {{ $totalMeetings }} <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted);">Pertemuan</span>
            </div>
            <div style="font-size: 0.78rem; color: #64748b; margin-top: 4px;">
                Kurikulum aktif {{ $currentSubject->name }}
            </div>
        </div>
    </div>

    <!-- Stat 2: Total Materials -->
    <div class="card" style="border-left: 4px solid #10b981; box-shadow: var(--shadow-sm); border-radius: var(--radius-md);">
        <div class="card-body" style="padding: 1.25rem;">
            <div style="font-size: 0.76rem; font-weight: 700; color: #065f46; text-transform: uppercase; letter-spacing: 0.5px;">
                Total Modul Slide Materi
            </div>
            <div style="font-size: 1.85rem; font-weight: 800; color: #10b981; margin-top: 4px;">
                {{ $totalMaterials }} <span style="font-size: 0.85rem; font-weight: 600; color: #059669;">Slide</span>
            </div>
            <div style="font-size: 0.78rem; color: #64748b; margin-top: 4px;">
                Terdistribusi pada pertemuan aktif
            </div>
        </div>
    </div>

    <!-- Stat 3: Total Exercises -->
    <div class="card" style="border-left: 4px solid #8b5cf6; box-shadow: var(--shadow-sm); border-radius: var(--radius-md);">
        <div class="card-body" style="padding: 1.25rem;">
            <div style="font-size: 0.76rem; font-weight: 700; color: #6d28d9; text-transform: uppercase; letter-spacing: 0.5px;">
                Total Bank Soal Latihan
            </div>
            <div style="font-size: 1.85rem; font-weight: 800; color: #8b5cf6; margin-top: 4px;">
                {{ $totalExercises }} <span style="font-size: 0.85rem; font-weight: 600; color: #7c3aed;">Butir Soal</span>
            </div>
            <div style="font-size: 0.78rem; color: #64748b; margin-top: 4px;">
                Soal interaktif untuk latihan siswa
            </div>
        </div>
    </div>
</div>

<!-- Meetings Table Card -->
<div class="card" style="box-shadow: var(--shadow-sm); border-radius: var(--radius-lg); overflow: hidden;">
    <div class="card-header" style="background: #ffffff; border-bottom: 1px solid var(--border-color); padding: 1.15rem 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div>
            <h2 style="font-size: 1.15rem; margin: 0; font-weight: 800; color: #0f172a;">
                📋 Daftar Pertemuan ({{ $currentSubject->name }})
            </h2>
            <div style="font-size: 0.82rem; color: var(--text-muted); margin-top: 2px;">
                Urutan menentukan alur belajar dan sistem buka bertahap bagi siswa.
            </div>
        </div>

        <button type="button" class="btn btn-primary btn-sm" onclick="openCreateMeetingModal()" style="font-weight: 700;">
            ➕ Tambah Pertemuan Baru
        </button>
    </div>

    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table" style="margin: 0;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                        <th style="width: 70px; text-align: center;">Urutan</th>
                        <th style="min-width: 170px;">Nama Pertemuan</th>
                        <th>Deskripsi Kompetensi / Materi</th>
                        <th style="width: 120px; text-align: center;">Syarat Poin</th>
                        <th style="width: 130px; text-align: center;">Materi Slide</th>
                        <th style="width: 130px; text-align: center;">Bank Soal</th>
                        <th style="width: 150px; text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($meetings as $meeting)
                        <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;">
                            <td style="text-align: center; vertical-align: middle;">
                                <div style="width: 34px; height: 34px; border-radius: 50%; background: #eff6ff; color: #1e40af; font-weight: 800; font-size: 0.88rem; display: inline-flex; align-items: center; justify-content: center; border: 1px solid #bfdbfe;">
                                    {{ $meeting->order }}
                                </div>
                            </td>
                            <td style="vertical-align: middle;">
                                <strong style="font-size: 0.98rem; color: #0f172a; display: block;">
                                    🗓️ {{ $meeting->name }}
                                </strong>
                                <span style="font-size: 0.74rem; color: #64748b;">
                                    ID Level: #{{ $meeting->id }}
                                </span>
                            </td>
                            <td style="vertical-align: middle; color: #334155; font-size: 0.86rem; line-height: 1.45;">
                                {{ $meeting->description ?: '-' }}
                            </td>
                            <td style="text-align: center; vertical-align: middle;">
                                <span class="badge" style="background: #fef3c7; color: #92400e; font-weight: 700; font-size: 0.78rem; border: 1px solid #fde68a;">
                                    {{ $meeting->required_points }} Poin
                                </span>
                            </td>
                            <td style="text-align: center; vertical-align: middle;">
                                <a href="{{ route('admin.materials.index', ['level_id' => $meeting->id]) }}" class="badge" style="background: #ecfdf5; color: #065f46; font-weight: 700; font-size: 0.78rem; border: 1px solid #a7f3d0; text-decoration: none;" title="Buka Modul Slide Pertemuan Ini">
                                    📑 {{ $meeting->materials_count }} Modul
                                </a>
                            </td>
                            <td style="text-align: center; vertical-align: middle;">
                                @if($currentSubject->id == 2)
                                    <a href="{{ route('admin.japanese.tests.index') }}" class="badge" style="background: #fdf2f8; color: #be185d; font-weight: 700; font-size: 0.78rem; border: 1px solid #fbcfe8; text-decoration: none;">
                                        ✍️ Tes Evaluasi
                                    </a>
                                @else
                                    <a href="{{ route('admin.exercises.index', ['level_id' => $meeting->id]) }}" class="badge {{ $meeting->exercises_count >= 10 ? 'badge-success' : 'badge-neutral' }}" style="font-size: 0.78rem; text-decoration: none;">
                                        🎯 {{ $meeting->exercises_count }} Soal
                                    </a>
                                @endif
                            </td>
                            <td style="text-align: right; vertical-align: middle;">
                                <div style="display: inline-flex; gap: 6px; align-items: center;">
                                    <!-- Edit Button -->
                                    <button type="button" class="btn btn-secondary btn-sm"
                                            onclick='openEditMeetingModal(@json($meeting))'
                                            style="padding: 4px 10px; font-weight: 700; font-size: 0.8rem;"
                                            title="Edit Pertemuan">
                                        ✏️ Edit
                                    </button>

                                    <!-- Delete Button -->
                                    <form action="{{ route('admin.meetings.destroy', $meeting) }}" method="POST" style="margin: 0;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus {{ addslashes($meeting->name) }}? Seluruh modul materi dan soal latihan pada pertemuan ini akan ikut terhapus.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" style="padding: 4px 10px; font-weight: 700; font-size: 0.8rem;" title="Hapus Pertemuan">
                                            🗑️ Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 3rem 1.5rem; color: var(--text-muted);">
                                <div style="font-size: 2rem; margin-bottom: 0.5rem;">🗓️</div>
                                <div style="font-weight: 700; font-size: 1.05rem; color: #0f172a;">Belum Ada Pertemuan</div>
                                <p style="font-size: 0.88rem; margin: 4px 0 1rem 0;">Belum ada pertemuan belajar yang dibuat untuk mata pelajaran {{ $currentSubject->name }}.</p>
                                <button type="button" class="btn btn-primary btn-sm" onclick="openCreateMeetingModal()">
                                    ➕ Tambah Pertemuan Pertama
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ================= MODAL: TAMBAH PERTEMUAN BARU ================= -->
<div id="createMeetingModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 9999; align-items: center; justify-content: center; padding: 1.5rem; backdrop-filter: blur(2px);">
    <div style="background: #ffffff; border-radius: var(--radius-lg); width: 100%; max-width: 540px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); overflow: hidden; animation: modalPop 0.2s ease-out;">
        <div style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 1.15rem 1.5rem; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 1.35rem;">🗓️</span>
                <h3 style="font-size: 1.15rem; margin: 0; font-weight: 800; color: #0f172a;">
                    Tambah Pertemuan Baru ({{ $currentSubject->name }})
                </h3>
            </div>
            <button type="button" onclick="closeCreateMeetingModal()" style="background: none; border: none; font-size: 1.5rem; line-height: 1; color: #64748b; cursor: pointer;">&times;</button>
        </div>

        <form action="{{ route('admin.meetings.store') }}" method="POST" style="margin: 0;">
            @csrf
            <input type="hidden" name="subject_id" value="{{ $currentSubject->id }}">

            <div style="padding: 1.5rem; display: flex; flex-direction: column; gap: 1.1rem;">
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 12px;">
                    <div>
                        <label class="form-label" for="create_name" style="font-weight: 700; font-size: 0.88rem; color: #0f172a;">
                            Nama Pertemuan *
                        </label>
                        <input type="text" name="name" id="create_name" class="form-control"
                               value="{{ old('name', $suggestedName) }}" required
                               placeholder="Contoh: Pertemuan {{ $nextOrder }}">
                    </div>

                    <div>
                        <label class="form-label" for="create_order" style="font-weight: 700; font-size: 0.88rem; color: #0f172a;">
                            Nomor Urut *
                        </label>
                        <input type="number" name="order" id="create_order" class="form-control"
                               value="{{ old('order', $nextOrder) }}" min="1" max="999" required>
                    </div>
                </div>

                <div>
                    <label class="form-label" for="create_description" style="font-weight: 700; font-size: 0.88rem; color: #0f172a;">
                        Deskripsi Materi / Kompetensi
                    </label>
                    <textarea name="description" id="create_description" class="form-control" rows="3"
                              placeholder="Tuliskan gambaran ringkas materi, topik pembelajaran, atau target kompetensi pertemuan ini...">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label class="form-label" for="create_required_points" style="font-weight: 700; font-size: 0.88rem; color: #0f172a;">
                        Syarat Poin Naik Pertemuan (Standar 100)
                    </label>
                    <input type="number" name="required_points" id="create_required_points" class="form-control"
                           value="{{ old('required_points', 100) }}" min="10" max="500">
                    <small style="font-size: 0.74rem; color: #64748b; margin-top: 3px; display: block;">
                        Siswa membutuhkan jumlah poin ini untuk menuntaskan pertemuan dan membuka materi pertemuan berikutnya.
                    </small>
                </div>
            </div>

            <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 1rem 1.5rem; display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" class="btn btn-secondary" onclick="closeCreateMeetingModal()">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary" style="font-weight: 700;">
                    💾 Simpan Pertemuan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: EDIT PERTEMUAN ================= -->
<div id="editMeetingModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 9999; align-items: center; justify-content: center; padding: 1.5rem; backdrop-filter: blur(2px);">
    <div style="background: #ffffff; border-radius: var(--radius-lg); width: 100%; max-width: 540px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); overflow: hidden; animation: modalPop 0.2s ease-out;">
        <div style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 1.15rem 1.5rem; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 1.35rem;">✏️</span>
                <h3 style="font-size: 1.15rem; margin: 0; font-weight: 800; color: #0f172a;" id="editModalTitle">
                    Edit Pertemuan
                </h3>
            </div>
            <button type="button" onclick="closeEditMeetingModal()" style="background: none; border: none; font-size: 1.5rem; line-height: 1; color: #64748b; cursor: pointer;">&times;</button>
        </div>

        <form id="editMeetingForm" method="POST" style="margin: 0;">
            @csrf
            @method('PUT')

            <div style="padding: 1.5rem; display: flex; flex-direction: column; gap: 1.1rem;">
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 12px;">
                    <div>
                        <label class="form-label" for="edit_name" style="font-weight: 700; font-size: 0.88rem; color: #0f172a;">
                            Nama Pertemuan *
                        </label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>

                    <div>
                        <label class="form-label" for="edit_order" style="font-weight: 700; font-size: 0.88rem; color: #0f172a;">
                            Nomor Urut *
                        </label>
                        <input type="number" name="order" id="edit_order" class="form-control" min="1" max="999" required>
                    </div>
                </div>

                <div>
                    <label class="form-label" for="edit_description" style="font-weight: 700; font-size: 0.88rem; color: #0f172a;">
                        Deskripsi Materi / Kompetensi
                    </label>
                    <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                </div>

                <div>
                    <label class="form-label" for="edit_required_points" style="font-weight: 700; font-size: 0.88rem; color: #0f172a;">
                        Syarat Poin Naik Pertemuan
                    </label>
                    <input type="number" name="required_points" id="edit_required_points" class="form-control" min="10" max="500">
                </div>
            </div>

            <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 1rem 1.5rem; display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" class="btn btn-secondary" onclick="closeEditMeetingModal()">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary" style="font-weight: 700;">
                    💾 Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateMeetingModal() {
        document.getElementById('createMeetingModal').style.display = 'flex';
        document.getElementById('create_name').focus();
    }

    function closeCreateMeetingModal() {
        document.getElementById('createMeetingModal').style.display = 'none';
    }

    function openEditMeetingModal(meeting) {
        document.getElementById('editMeetingForm').action = '/admin/meetings/' + meeting.id;
        document.getElementById('editModalTitle').textContent = 'Edit ' + meeting.name;
        document.getElementById('edit_name').value = meeting.name;
        document.getElementById('edit_order').value = meeting.order;
        document.getElementById('edit_description').value = meeting.description || '';
        document.getElementById('edit_required_points').value = meeting.required_points || 100;

        document.getElementById('editMeetingModal').style.display = 'flex';
        document.getElementById('edit_name').focus();
    }

    function closeEditMeetingModal() {
        document.getElementById('editMeetingModal').style.display = 'none';
    }

    // Close on Escape or click outside
    window.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeCreateMeetingModal();
            closeEditMeetingModal();
        }
    });

    document.getElementById('createMeetingModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeCreateMeetingModal();
    });

    document.getElementById('editMeetingModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeEditMeetingModal();
    });
</script>

<style>
    @keyframes modalPop {
        from {
            opacity: 0;
            transform: scale(0.96);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
</style>
@endsection
