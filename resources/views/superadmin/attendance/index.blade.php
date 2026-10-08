@extends('layouts.app')

@section('title', 'Riwayat & Kelola Absensi Guru & Siswa - Super Admin - Musashi')

@section('content')
<div style="margin-bottom: 2rem;">
    <!-- 3 Subject Selector Pills -->
    <div style="display: flex; gap: 10px; margin-bottom: 1.5rem; flex-wrap: wrap;">
        @foreach($allSubjects as $subj)
            @php
                $isActiveSubj = $subj->id === $currentSubject->id;
            @endphp
            <a href="{{ route('superadmin.attendance.index', ['subject_id' => $subj->id, 'tab' => $activeTab]) }}"
               class="btn {{ $isActiveSubj ? 'btn-primary' : 'btn-secondary' }}"
               style="border-radius: 9999px; font-weight: 800; padding: 8px 22px; font-size: 0.95rem; display: inline-flex; align-items: center; box-shadow: {{ $isActiveSubj ? '0 4px 6px -1px rgba(37, 99, 235, 0.25)' : 'none' }};">
                <span>{{ $subj->name }}</span>
            </a>
        @endforeach
    </div>

    <!-- Header & Action Buttons -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1.25rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px; flex-wrap: wrap;">
                <span class="badge" style="background: #eef2ff; color: #4338ca; font-weight: 800; font-size: 0.8rem; border: 1px solid #c7d2fe;">
                    Learning Musashi Super Admin
                </span>
                <span class="badge" style="background: #eff6ff; color: #1e40af; font-weight: 800; font-size: 0.8rem; border: 1px solid #bfdbfe;">
                    {{ $currentSubject->name }}
                </span>
                <span class="badge" style="background: #f1f5f9; color: #475569; font-weight: 700; font-size: 0.8rem;">
                    Mode: {{ $activeTab === 'guru' ? 'Kelola Guru' : 'Kelola Siswa' }}
                </span>
            </div>
            <h1 style="font-size: 1.85rem; margin: 0; font-weight: 800; color: #0f172a;">
                ⏱️ Riwayat & Kelola Absensi: {{ $currentSubject->name }}
            </h1>
            <p style="color: var(--text-muted); font-size: 0.92rem; margin-top: 4px; max-width: 720px;">
                Pantau rekaman presensi kehadiran guru dan siswa, ubah kehadiran (termasuk status belum absen), hapus catatan, reset massal, serta unduh rekap data dalam format Excel.
            </p>
        </div>

        <!-- Action Buttons -->
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <!-- Download Button -->
            <button type="button" class="btn btn-secondary" onclick="openExportModal()" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; padding: 9px 16px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm);">
                <span style="font-size: 1.1rem;">📥</span>
                <span>Download Rekap Excel</span>
            </button>

            <!-- Manual Input Button -->
            <button type="button" class="btn btn-primary" onclick="openCreateModal()" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; padding: 9px 18px; border-radius: var(--radius-md); box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.25);">
                <span style="font-size: 1.1rem;">➕</span>
                <span>Catat Presensi Manual</span>
            </button>

            <!-- Reset All Button -->
            <button type="button" class="btn btn-danger" onclick="openResetModal()" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; padding: 9px 16px; border-radius: var(--radius-md); background: #dc2626; border-color: #dc2626; color: #fff;">
                <span style="font-size: 1.1rem;">🗑️</span>
                <span>Reset Semua Absen</span>
            </button>
        </div>
    </div>
</div>

<!-- Sub-Tabs: Riwayat Absen Guru vs Riwayat Absen Siswa -->
<div style="display: flex; gap: 8px; margin-bottom: 1.75rem; border-bottom: 2px solid #e2e8f0; padding-bottom: 6px;">
    <a href="{{ route('superadmin.attendance.index', ['subject_id' => $currentSubject->id, 'tab' => 'guru']) }}"
       style="display: flex; align-items: center; gap: 8px; padding: 10px 20px; font-weight: 800; font-size: 0.95rem; text-decoration: none; border-bottom: 3px solid {{ $activeTab === 'guru' ? '#2563eb' : 'transparent' }}; color: {{ $activeTab === 'guru' ? '#2563eb' : '#64748b' }}; margin-bottom: -8px;">
        <span style="font-size: 1.15rem;">👨‍🏫</span>
        <span>Riwayat Absen Guru ({{ $teachers->count() }})</span>
    </a>
    <a href="{{ route('superadmin.attendance.index', ['subject_id' => $currentSubject->id, 'tab' => 'siswa']) }}"
       style="display: flex; align-items: center; gap: 8px; padding: 10px 20px; font-weight: 800; font-size: 0.95rem; text-decoration: none; border-bottom: 3px solid {{ $activeTab === 'siswa' ? '#2563eb' : 'transparent' }}; color: {{ $activeTab === 'siswa' ? '#2563eb' : '#64748b' }}; margin-bottom: -8px;">
        <span style="font-size: 1.15rem;">🎓</span>
        <span>Riwayat Absen Siswa ({{ $students->count() }})</span>
    </a>
</div>

<!-- Stats Overview Cards -->
<div class="grid grid-cols-4" style="gap: 1.15rem; margin-bottom: 1.75rem;">
    <!-- Stat 1: Total Users -->
    <div class="card" style="border-left: 4px solid #2563eb; box-shadow: var(--shadow-sm); border-radius: var(--radius-md);">
        <div class="card-body" style="padding: 1.15rem;">
            <div style="font-size: 0.74rem; font-weight: 700; color: #1e40af; text-transform: uppercase;">
                {{ $activeTab === 'guru' ? 'Total Guru Terdaftar' : 'Total Siswa Terdaftar' }}
            </div>
            <div style="font-size: 1.65rem; font-weight: 800; color: #2563eb; margin-top: 3px;">
                {{ $stats['total_users'] }} <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">Orang</span>
            </div>
        </div>
    </div>

    <!-- Stat 2: Total Records -->
    <div class="card" style="border-left: 4px solid #64748b; box-shadow: var(--shadow-sm); border-radius: var(--radius-md);">
        <div class="card-body" style="padding: 1.15rem;">
            <div style="font-size: 0.74rem; font-weight: 700; color: #475569; text-transform: uppercase;">
                Total Log Presensi
            </div>
            <div style="font-size: 1.65rem; font-weight: 800; color: #334155; margin-top: 3px;">
                {{ $stats['total_records'] }} <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">Sesi</span>
            </div>
        </div>
    </div>

    <!-- Stat 3: Hadir -->
    <div class="card" style="border-left: 4px solid #10b981; box-shadow: var(--shadow-sm); border-radius: var(--radius-md);">
        <div class="card-body" style="padding: 1.15rem;">
            <div style="font-size: 0.74rem; font-weight: 700; color: #065f46; text-transform: uppercase;">
                Presensi Hadir
            </div>
            <div style="font-size: 1.65rem; font-weight: 800; color: #10b981; margin-top: 3px;">
                {{ $stats['total_hadir'] }} <span style="font-size: 0.8rem; font-weight: 600; color: #059669;">Kali</span>
            </div>
        </div>
    </div>

    <!-- Stat 4: Attendance Rate -->
    <div class="card" style="border-left: 4px solid #f59e0b; box-shadow: var(--shadow-sm); border-radius: var(--radius-md);">
        <div class="card-body" style="padding: 1.15rem;">
            <div style="font-size: 0.74rem; font-weight: 700; color: #b45309; text-transform: uppercase;">
                Tingkat Kehadiran
            </div>
            <div style="font-size: 1.65rem; font-weight: 800; color: #d97706; margin-top: 3px;">
                {{ $stats['rate'] }}%
            </div>
        </div>
    </div>
</div>

<!-- Filters Bar -->
<div class="card" style="margin-bottom: 1.5rem; box-shadow: var(--shadow-sm); border-radius: var(--radius-md);">
    <div class="card-body" style="padding: 1.15rem 1.5rem;">
        <form method="GET" action="{{ route('superadmin.attendance.index') }}" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <input type="hidden" name="subject_id" value="{{ $currentSubject->id }}">
            <input type="hidden" name="tab" value="{{ $activeTab }}">

            <div style="min-width: 170px;">
                <label style="font-size: 0.76rem; font-weight: 700; color: #64748b; display: block; margin-bottom: 3px;">Pilih Tanggal</label>
                <input type="date" name="date" class="form-control" value="{{ $selectedDate }}">
            </div>

            <div style="min-width: 170px;">
                <label style="font-size: 0.76rem; font-weight: 700; color: #64748b; display: block; margin-bottom: 3px;">Pilih Bulan</label>
                <input type="month" name="month" class="form-control" value="{{ $selectedMonth }}">
            </div>

            @if($activeTab === 'siswa' && $classes->count() > 0)
                <div style="min-width: 170px;">
                    <label style="font-size: 0.76rem; font-weight: 700; color: #64748b; display: block; margin-bottom: 3px;">Kelas / Grup</label>
                    <select name="class_name" class="form-control">
                        <option value="all">Semua Kelas/Grup</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->name }}" {{ $selectedClass === $c->name ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div style="min-width: 150px;">
                <label style="font-size: 0.76rem; font-weight: 700; color: #64748b; display: block; margin-bottom: 3px;">Status Kehadiran</label>
                <select name="status" class="form-control">
                    <option value="all">Semua Status</option>
                    <option value="hadir" {{ $statusFilter === 'hadir' ? 'selected' : '' }}>Hadir</option>
                    <option value="izin_keterangan" {{ $statusFilter === 'izin_keterangan' ? 'selected' : '' }}>Izin (Keterangan)</option>
                    <option value="izin_tanpa_keterangan" {{ $statusFilter === 'izin_tanpa_keterangan' ? 'selected' : '' }}>Izin (Tanpa Keterangan)</option>
                </select>
            </div>

            <div style="flex: 1; min-width: 200px;">
                <label style="font-size: 0.76rem; font-weight: 700; color: #64748b; display: block; margin-bottom: 3px;">Cari Nama / Email</label>
                <input type="text" name="search" class="form-control" placeholder="Cari nama, email, kelas, divisi..." value="{{ $search }}">
            </div>

            <div style="margin-top: 18px; display: flex; gap: 6px;">
                <button type="submit" class="btn btn-primary btn-sm" style="font-weight: 700;">
                    🔍 Terapkan Filter
                </button>
                <a href="{{ route('superadmin.attendance.index', ['subject_id' => $currentSubject->id, 'tab' => $activeTab]) }}" class="btn btn-secondary btn-sm">
                    Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Selection Floating Bar (shows when items are checked) -->
<div id="bulkActionBar" style="display: none; background: #0f172a; color: #ffffff; padding: 12px 20px; border-radius: var(--radius-md); margin-bottom: 1rem; align-items: center; justify-content: space-between; box-shadow: var(--shadow-lg); animation: fadeIn 0.2s ease;">
    <div style="display: flex; align-items: center; gap: 12px;">
        <span style="background: #2563eb; color: #ffffff; padding: 4px 10px; border-radius: 9999px; font-weight: 800; font-size: 0.82rem;" id="bulkSelectedCount">0</span>
        <span style="font-weight: 600; font-size: 0.92rem;">Data presensi dipilih</span>
    </div>
    <div style="display: flex; align-items: center; gap: 10px;">
        <button type="button" class="btn btn-secondary btn-sm" onclick="clearBulkSelection()" style="background: rgba(255,255,255,0.15); border: none; color: #fff;">
            Batal Pilihan
        </button>
        <button type="button" class="btn btn-danger btn-sm" onclick="submitBulkDelete()" style="background: #ef4444; border: none; font-weight: 700;">
            🗑️ Hapus Presensi Terpilih
        </button>
    </div>
</div>

<!-- Form for Bulk Deletion -->
<form id="bulkDeleteForm" action="{{ route('superadmin.attendance.bulk-destroy') }}" method="POST" style="display: none;">
    @csrf
    <div id="bulkDeleteInputsContainer"></div>
</form>

<!-- Attendance Table -->
<div class="card" style="box-shadow: var(--shadow-sm); border-radius: var(--radius-lg); overflow: hidden;">
    <div class="card-header" style="background: #ffffff; border-bottom: 1px solid var(--border-color); padding: 1.15rem 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <h2 style="font-size: 1.15rem; margin: 0; font-weight: 800; color: #0f172a;">
            📋 Rekap {{ $activeTab === 'guru' ? 'Absensi Guru' : 'Absensi Siswa' }} ({{ $currentSubject->name }})
        </h2>
        <div style="font-size: 0.85rem; color: #64748b;">
            Menampilkan {{ $attendances->count() }} dari {{ $attendances->total() }} catatan presensi
        </div>
    </div>

    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table" style="margin: 0;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                        <th style="width: 44px; text-align: center; vertical-align: middle;">
                            <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)" style="cursor: pointer; width: 16px; height: 16px;">
                        </th>
                        <th style="width: 50px; text-align: center;">No</th>
                        <th style="width: 155px;">Tanggal & Waktu</th>
                        <th>{{ $activeTab === 'guru' ? 'Nama Pengajar' : 'Nama Siswa' }}</th>
                        @if($activeTab === 'siswa')
                            <th>Kelas / Grup & Divisi</th>
                        @endif
                        <th style="width: 150px; text-align: center;">Status Presensi</th>
                        <th>Catatan / Keterangan</th>
                        <th style="width: 170px; text-align: center;">Aksi Super Admin</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $index => $att)
                        @php
                            $userName = $att->user->name ?? 'Pengguna Tidak Diketahui';
                            $userEmail = $att->user->email ?? '-';
                            $attDateFormatted = $att->date->format('Y-m-d');
                            $attTimeFormatted = $att->check_in_time ? substr($att->check_in_time, 0, 5) : '';
                        @endphp
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="text-align: center; vertical-align: middle;">
                                <input type="checkbox" class="attendance-select-row" value="{{ $att->id }}" onchange="handleRowSelect()" style="cursor: pointer; width: 16px; height: 16px;">
                            </td>
                            <td style="text-align: center; vertical-align: middle; color: #64748b; font-size: 0.85rem;">
                                {{ $attendances->firstItem() + $index }}
                            </td>
                            <td style="vertical-align: middle;">
                                <strong style="color: #0f172a; display: block; font-size: 0.9rem;">
                                    📅 {{ $att->date->format('d M Y') }}
                                </strong>
                                <span style="font-size: 0.76rem; color: #64748b;">
                                    ⏰ {{ $att->check_in_time ? substr($att->check_in_time, 0, 5) . ' WIB' : '-' }}
                                </span>
                            </td>
                            <td style="vertical-align: middle;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div class="avatar" style="width: 32px; height: 32px; font-size: 0.82rem; font-weight: 800; background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        {{ strtoupper(substr($userName, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; color: #0f172a; font-size: 0.92rem;">
                                            {{ $userName }}
                                        </div>
                                        <div style="font-size: 0.74rem; color: #64748b;">
                                            {{ $userEmail }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            @if($activeTab === 'siswa')
                                <td style="vertical-align: middle;">
                                    <span class="badge" style="background: #eff6ff; color: #1e40af; font-weight: 700; font-size: 0.74rem;">
                                        {{ $att->user->class_name ?: 'Belum Ada Kelas' }}
                                    </span>
                                    <span style="font-size: 0.74rem; color: #64748b; display: block; margin-top: 2px;">
                                        {{ $att->user->division ?: 'Divisi Tidak Disebutkan' }}
                                    </span>
                                </td>
                            @endif
                            <td style="text-align: center; vertical-align: middle;">
                                @if($att->status === 'hadir')
                                    <span class="badge" style="background: #ecfdf5; color: #065f46; font-weight: 800; font-size: 0.76rem; border: 1px solid #a7f3d0; padding: 4px 10px;">
                                        ✅ Hadir
                                    </span>
                                @elseif($att->status === 'izin_keterangan')
                                    <span class="badge" style="background: #eff6ff; color: #1e40af; font-weight: 700; font-size: 0.76rem; border: 1px solid #bfdbfe; padding: 4px 10px;">
                                        📝 Izin (Keterangan)
                                    </span>
                                @else
                                    <span class="badge" style="background: #fef2f2; color: #991b1b; font-weight: 700; font-size: 0.76rem; border: 1px solid #fecaca; padding: 4px 10px;">
                                        ⚠️ Izin (Tanpa Ket.)
                                    </span>
                                @endif
                            </td>
                            <td style="vertical-align: middle; color: #334155; font-size: 0.85rem;">
                                {{ $att->notes ?: '-' }}
                            </td>
                            <td style="text-align: center; vertical-align: middle;">
                                <div style="display: flex; align-items: center; justify-content: center; gap: 6px;">
                                    <!-- Kelola / Edit Button -->
                                    <button type="button"
                                            class="btn btn-secondary btn-sm"
                                            style="font-size: 0.78rem; font-weight: 700; padding: 5px 10px; border-radius: var(--radius-sm);"
                                            onclick="openEditModal({{ $att->id }}, '{{ addslashes($userName) }}', '{{ $attDateFormatted }}', '{{ $attTimeFormatted }}', '{{ $att->status }}', '{{ addslashes($att->notes ?? '') }}')">
                                        ✏️ Kelola
                                    </button>

                                    <!-- Delete Button -->
                                    <form action="{{ route('superadmin.attendance.destroy', $att->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data presensi ini? Status pengguna akan kembali menjadi Belum Absen.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" style="font-size: 0.78rem; font-weight: 700; padding: 5px 10px; border-radius: var(--radius-sm); background: #fee2e2; color: #991b1b; border: 1px solid #fecaca;">
                                            🗑️ Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $activeTab === 'siswa' ? 8 : 7 }}" style="text-align: center; padding: 3rem 1.5rem; color: var(--text-muted);">
                                <div style="font-size: 2.2rem; margin-bottom: 0.5rem;">⏱️</div>
                                <div style="font-weight: 700; font-size: 1.05rem; color: #0f172a;">Belum Ada Riwayat Presensi</div>
                                <p style="font-size: 0.88rem; margin: 4px 0 0 0;">
                                    Belum ada catatan presensi untuk {{ $activeTab === 'guru' ? 'pengajar' : 'siswa' }} pada mata pelajaran {{ $currentSubject->name }}.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($attendances->hasPages())
            <div style="padding: 1.25rem 1.5rem; background: #ffffff; border-top: 1px solid var(--border-color);">
                {{ $attendances->links() }}
            </div>
        @endif
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 1: KELOLA / EDIT PRESENSI (DENGAN PILIHAN BELUM ABSEN) -->
<!-- ========================================== -->
<div id="editAttendanceModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #ffffff; border-radius: var(--radius-lg); max-width: 520px; width: 100%; box-shadow: var(--shadow-xl); overflow: hidden; animation: fadeIn 0.2s ease;">
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <div>
                <h3 style="font-size: 1.15rem; margin: 0; color: #0f172a; font-weight: 800;">
                    ✏️ Kelola Status Presensi
                </h3>
                <span style="font-size: 0.78rem; color: #64748b;">Ubah status kehadiran, catatan, atau reset menjadi belum absen</span>
            </div>
            <button type="button" onclick="closeEditModal()" style="background:none; border:none; font-size: 1.5rem; cursor: pointer; color: var(--text-muted); line-height: 1;">&times;</button>
        </div>

        <form id="editAttendanceForm" action="" method="POST">
            @csrf
            @method('PUT')
            <div style="padding: 1.5rem;">
                <!-- User Info Box -->
                <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: var(--radius-md); padding: 12px 16px; margin-bottom: 1.25rem;">
                    <div style="font-size: 0.72rem; font-weight: 700; color: #1e40af; text-transform: uppercase;">
                        Pengguna ({{ $activeTab === 'guru' ? 'Guru' : 'Siswa' }})
                    </div>
                    <div style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin-top: 2px;" id="editModalUserName">
                        -
                    </div>
                </div>

                <div class="grid grid-cols-2" style="gap: 12px; margin-bottom: 1rem;">
                    <div>
                        <label class="form-label" style="font-weight: 700; font-size: 0.82rem;">Tanggal Presensi</label>
                        <input type="date" name="date" id="editModalDate" class="form-control" required>
                    </div>
                    <div>
                        <label class="form-label" style="font-weight: 700; font-size: 0.82rem;">Jam Presensi (WIB)</label>
                        <input type="time" name="check_in_time" id="editModalTime" class="form-control" step="1">
                    </div>
                </div>

                <!-- Status Presensi -->
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label class="form-label" style="font-weight: 700; font-size: 0.82rem; color: #0f172a;">
                        Status Presensi *
                    </label>
                    <select name="status" id="editModalStatus" class="form-control" required onchange="handleEditStatusChange(this.value)" style="font-weight: 700;">
                        <option value="hadir">✅ Hadir</option>
                        <option value="izin_keterangan">📝 Izin dengan Keterangan</option>
                        <option value="izin_tanpa_keterangan">⚠️ Izin tanpa Keterangan</option>
                        <option value="belum_absen" style="color: #dc2626; font-weight: 800;">⏳ Belum Absen (Hapus / Reset Absensi)</option>
                    </select>

                    <!-- Notice if "belum_absen" is selected -->
                    <div id="editBelumAbsenAlert" style="display: none; margin-top: 8px; padding: 10px 14px; background: #fef2f2; border: 1px solid #fecaca; border-radius: var(--radius-sm); font-size: 0.82rem; color: #991b1b; line-height: 1.4;">
                        ℹ️ <strong>Perhatian:</strong> Memilih <em>"Belum Absen"</em> akan menghapus catatan presensi ini dari database. Status kehadiran pengguna untuk tanggal ini akan otomatis kembali menjadi <strong>Belum Absen</strong>.
                    </div>
                </div>

                <!-- Catatan / Keterangan Izin -->
                <div class="form-group" id="editModalNotesGroup" style="margin-bottom: 0.5rem;">
                    <label class="form-label" style="font-weight: 700; font-size: 0.82rem;">Keterangan / Alasan Izin</label>
                    <textarea name="notes" id="editModalNotes" class="form-control" rows="3" placeholder="Tulis alasan izin atau catatan tambahan..."></textarea>
                </div>
            </div>

            <div style="padding: 1rem 1.5rem; background: #f8fafc; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Batal</button>
                <button type="submit" class="btn btn-primary" style="font-weight: 700;">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 2: CATAT PRESENSI MANUAL -->
<!-- ========================================== -->
<div id="createAttendanceModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #ffffff; border-radius: var(--radius-lg); max-width: 520px; width: 100%; box-shadow: var(--shadow-xl); overflow: hidden; animation: fadeIn 0.2s ease;">
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <div>
                <h3 style="font-size: 1.15rem; margin: 0; color: #0f172a; font-weight: 800;">
                    ➕ Input Presensi Manual
                </h3>
                <span style="font-size: 0.78rem; color: #64748b;">Tambahkan atau ubah catatan presensi untuk {{ $activeTab === 'guru' ? 'Guru' : 'Siswa' }}</span>
            </div>
            <button type="button" onclick="closeCreateModal()" style="background:none; border:none; font-size: 1.5rem; cursor: pointer; color: var(--text-muted); line-height: 1;">&times;</button>
        </div>

        <form action="{{ route('superadmin.attendance.store') }}" method="POST">
            @csrf
            <div style="padding: 1.5rem;">
                <!-- User Selector -->
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label" style="font-weight: 700; font-size: 0.82rem; color: #0f172a;">
                        Pilih {{ $activeTab === 'guru' ? 'Guru' : 'Siswa' }} *
                    </label>
                    <select name="user_id" class="form-control" required style="font-weight: 600;">
                        <option value="">-- Pilih {{ $activeTab === 'guru' ? 'Guru' : 'Siswa' }} --</option>
                        @if($activeTab === 'guru')
                            @foreach($teachers as $t)
                                <option value="{{ $t->id }}">
                                    {{ $t->name }} ({{ $t->email }})
                                </option>
                            @endforeach
                        @else
                            @foreach($students as $s)
                                <option value="{{ $s->id }}">
                                    {{ $s->name }} {{ $s->class_name ? '• ' . $s->class_name : '' }} ({{ $s->email }})
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div class="grid grid-cols-2" style="gap: 12px; margin-bottom: 1rem;">
                    <div>
                        <label class="form-label" style="font-weight: 700; font-size: 0.82rem;">Tanggal Presensi *</label>
                        <input type="date" name="date" class="form-control" value="{{ now()->toDateString() }}" required>
                    </div>
                    <div>
                        <label class="form-label" style="font-weight: 700; font-size: 0.82rem;">Jam Presensi (WIB)</label>
                        <input type="time" name="check_in_time" class="form-control" value="{{ now()->format('H:i') }}" step="1">
                    </div>
                </div>

                <!-- Status Presensi -->
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label class="form-label" style="font-weight: 700; font-size: 0.82rem; color: #0f172a;">
                        Status Presensi *
                    </label>
                    <select name="status" class="form-control" required onchange="handleCreateStatusChange(this.value)" style="font-weight: 700;">
                        <option value="hadir">✅ Hadir</option>
                        <option value="izin_keterangan">📝 Izin dengan Keterangan</option>
                        <option value="izin_tanpa_keterangan">⚠️ Izin tanpa Keterangan</option>
                        <option value="belum_absen" style="color: #dc2626; font-weight: 800;">⏳ Belum Absen (Reset / Hapus Absensi)</option>
                    </select>

                    <div id="createBelumAbsenAlert" style="display: none; margin-top: 8px; padding: 10px 14px; background: #fef2f2; border: 1px solid #fecaca; border-radius: var(--radius-sm); font-size: 0.82rem; color: #991b1b; line-height: 1.4;">
                        ℹ️ Jika memilih <em>"Belum Absen"</em>, presensi tanggal tersebut untuk pengguna terpilih akan dihapus dari sistem.
                    </div>
                </div>

                <!-- Notes -->
                <div class="form-group" id="createModalNotesGroup" style="margin-bottom: 0.5rem;">
                    <label class="form-label" style="font-weight: 700; font-size: 0.82rem;">Keterangan / Alasan Izin</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Tuliskan keterangan jika memilih izin..."></textarea>
                </div>
            </div>

            <div style="padding: 1rem 1.5rem; background: #f8fafc; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary" onclick="closeCreateModal()">Batal</button>
                <button type="submit" class="btn btn-primary" style="font-weight: 700;">Simpan Presensi</button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 3: RESET SEMUA ABSEN (BULK / SUBJECT RESET) -->
<!-- ========================================== -->
<div id="resetAttendanceModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #ffffff; border-radius: var(--radius-lg); max-width: 540px; width: 100%; box-shadow: var(--shadow-xl); overflow: hidden; animation: fadeIn 0.2s ease;">
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #fee2e2; display: flex; justify-content: space-between; align-items: center; background: #fef2f2;">
            <div>
                <h3 style="font-size: 1.18rem; margin: 0; color: #991b1b; font-weight: 800;">
                    🗑️ Reset / Hapus Massal Presensi
                </h3>
                <span style="font-size: 0.78rem; color: #b91c1c;">Hapus data presensi sekaligus dan kembalikan ke status belum absen</span>
            </div>
            <button type="button" onclick="closeResetModal()" style="background:none; border:none; font-size: 1.5rem; cursor: pointer; color: #991b1b; line-height: 1;">&times;</button>
        </div>

        <form action="{{ route('superadmin.attendance.reset-all') }}" method="POST">
            @csrf
            <input type="hidden" name="subject_id" value="{{ $currentSubject->id }}">
            <input type="hidden" name="tab" value="{{ $activeTab }}">
            <input type="hidden" name="date" value="{{ $selectedDate }}">
            <input type="hidden" name="month" value="{{ $selectedMonth }}">
            <input type="hidden" name="class_name" value="{{ $selectedClass }}">
            <input type="hidden" name="status" value="{{ $statusFilter }}">
            <input type="hidden" name="search" value="{{ $search }}">

            <div style="padding: 1.5rem;">
                <div style="background: #fffbeb; border: 1px solid #fef3c7; border-radius: var(--radius-md); padding: 12px 16px; margin-bottom: 1.25rem; font-size: 0.85rem; color: #92400e; line-height: 1.45;">
                    ⚠️ <strong>Peringatan Penting:</strong> Tindakan ini akan menghapus catatan presensi yang dipilih secara permanen dari sistem. Semua pengguna yang terdampak akan otomatis berubah menjadi status <strong>Belum Absen</strong>.
                </div>

                <!-- Scope Options -->
                <label style="font-size: 0.82rem; font-weight: 700; color: #0f172a; display: block; margin-bottom: 8px;">
                    Pilih Cakupan Reset:
                </label>

                <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 1.25rem;">
                    @if($selectedDate || $selectedMonth || ($selectedClass && $selectedClass !== 'all') || ($statusFilter && $statusFilter !== 'all') || $search)
                        <label style="display: flex; align-items: flex-start; gap: 10px; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: var(--radius-md); cursor: pointer; background: #f8fafc;">
                            <input type="radio" name="scope" value="filtered" checked style="margin-top: 3px;">
                            <div>
                                <strong style="color: #0f172a; font-size: 0.9rem;">Hanya Data Sesuai Filter Aktif Saat Ini</strong>
                                <span style="display: block; font-size: 0.78rem; color: #64748b;">Mereset hanya data yang tampil pada filter saat ini ({{ $attendances->total() }} catatan).</span>
                            </div>
                        </label>
                    @endif

                    <label style="display: flex; align-items: flex-start; gap: 10px; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: var(--radius-md); cursor: pointer; background: #f8fafc;">
                        <input type="radio" name="scope" value="tab" {{ (!$selectedDate && !$selectedMonth && (!$selectedClass || $selectedClass === 'all') && (!$statusFilter || $statusFilter === 'all') && !$search) ? 'checked' : '' }} style="margin-top: 3px;">
                        <div>
                            <strong style="color: #0f172a; font-size: 0.9rem;">
                                Seluruh Absensi {{ $activeTab === 'guru' ? 'Guru' : 'Siswa' }} pada {{ $currentSubject->name }}
                            </strong>
                            <span style="display: block; font-size: 0.78rem; color: #64748b;">
                                Menghapus semua riwayat presensi {{ $activeTab === 'guru' ? 'guru' : 'siswa' }} mata pelajaran ini.
                            </span>
                        </div>
                    </label>

                    <label style="display: flex; align-items: flex-start; gap: 10px; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: var(--radius-md); cursor: pointer; background: #f8fafc;">
                        <input type="radio" name="scope" value="subject" style="margin-top: 3px;">
                        <div>
                            <strong style="color: #0f172a; font-size: 0.9rem;">
                                Seluruh Absensi (Guru & Siswa) pada {{ $currentSubject->name }}
                            </strong>
                            <span style="display: block; font-size: 0.78rem; color: #64748b;">
                                Menghapus semua riwayat presensi baik guru maupun siswa pada mata pelajaran {{ $currentSubject->name }}.
                            </span>
                        </div>
                    </label>
                </div>

                <!-- Confirmation Word Input -->
                <div class="form-group" style="margin-bottom: 0.5rem;">
                    <label class="form-label" style="font-weight: 700; font-size: 0.82rem; color: #991b1b;">
                        Ketik kata <span style="font-family: monospace; background: #fee2e2; padding: 2px 6px; border-radius: 4px;">RESET</span> untuk konfirmasi: *
                    </label>
                    <input type="text" name="confirm_text" id="resetConfirmInput" class="form-control" placeholder="Ketik RESET..." required autocomplete="off" oninput="validateResetButton(this.value)">
                </div>
            </div>

            <div style="padding: 1rem 1.5rem; background: #f8fafc; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary" onclick="closeResetModal()">Batal</button>
                <button type="submit" id="resetSubmitBtn" class="btn btn-danger" disabled style="background: #dc2626; border-color: #dc2626; font-weight: 700; opacity: 0.6; cursor: not-allowed;">
                    Konfirmasi Hapus / Reset
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 4: DOWNLOAD REKAP EXCEL PER MAPEL -->
<!-- ========================================== -->
<div id="exportModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #ffffff; border-radius: var(--radius-lg); max-width: 500px; width: 100%; box-shadow: var(--shadow-xl); overflow: hidden; animation: fadeIn 0.2s ease;">
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <div>
                <h3 style="font-size: 1.15rem; margin: 0; color: #0f172a; font-weight: 800;">
                    📥 Download Rekap Absensi (Excel)
                </h3>
                <span style="font-size: 0.78rem; color: #64748b;">Mata Pelajaran: <strong>{{ $currentSubject->name }}</strong></span>
            </div>
            <button type="button" onclick="closeExportModal()" style="background:none; border:none; font-size: 1.5rem; cursor: pointer; color: var(--text-muted); line-height: 1;">&times;</button>
        </div>

        <form action="{{ route('superadmin.attendance.export') }}" method="GET">
            <input type="hidden" name="subject_id" value="{{ $currentSubject->id }}">
            <input type="hidden" name="date" value="{{ $selectedDate }}">
            <input type="hidden" name="month" value="{{ $selectedMonth }}">
            <input type="hidden" name="class_name" value="{{ $selectedClass }}">
            <input type="hidden" name="status" value="{{ $statusFilter }}">
            <input type="hidden" name="search" value="{{ $search }}">

            <div style="padding: 1.5rem;">
                <label style="font-size: 0.82rem; font-weight: 700; color: #0f172a; display: block; margin-bottom: 8px;">
                    Pilih Lembar / Kategori yang Akan Diunduh:
                </label>

                <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 1.25rem;">
                    <label style="display: flex; align-items: flex-start; gap: 10px; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: var(--radius-md); cursor: pointer; background: #f8fafc;">
                        <input type="radio" name="tab" value="{{ $activeTab }}" checked style="margin-top: 3px;">
                        <div>
                            <strong style="color: #0f172a; font-size: 0.9rem;">
                                Tab Aktif: Absensi {{ $activeTab === 'guru' ? 'Guru' : 'Siswa' }} ({{ $currentSubject->name }})
                            </strong>
                            <span style="display: block; font-size: 0.78rem; color: #64748b;">
                                Mengunduh rekap {{ $activeTab === 'guru' ? 'guru' : 'siswa' }} beserta filter aktif.
                            </span>
                        </div>
                    </label>

                    <label style="display: flex; align-items: flex-start; gap: 10px; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: var(--radius-md); cursor: pointer; background: #f8fafc;">
                        <input type="radio" name="tab" value="both" style="margin-top: 3px;">
                        <div>
                            <strong style="color: #0f172a; font-size: 0.9rem;">
                                Lengkap: Absensi Guru & Siswa (2 Sheet Sekaligus)
                            </strong>
                            <span style="display: block; font-size: 0.78rem; color: #64748b;">
                                File Excel akan memiliki 2 worksheet terpisah: "Absensi Siswa" dan "Absensi Guru".
                            </span>
                        </div>
                    </label>
                </div>

                <div style="background: #f1f5f9; border-radius: var(--radius-sm); padding: 10px 14px; font-size: 0.8rem; color: #475569;">
                    💡 Filter yang sedang aktif (tanggal, bulan, kelas, status, pencarian) akan otomatis disertakan dalam laporan Excel yang dihasilkan.
                </div>
            </div>

            <div style="padding: 1rem 1.5rem; background: #f8fafc; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary" onclick="closeExportModal()">Batal</button>
                <button type="submit" class="btn btn-primary" style="font-weight: 700;">
                    📥 Unduh File Excel (.xlsx)
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// ==========================================
// EDIT MODAL LOGIC
// ==========================================
function openEditModal(id, name, date, time, status, notes) {
    const form = document.getElementById('editAttendanceForm');
    form.action = "{{ url('superadmin/attendance') }}/" + id;

    document.getElementById('editModalUserName').innerText = name;
    document.getElementById('editModalDate').value = date;
    document.getElementById('editModalTime').value = time;
    document.getElementById('editModalStatus').value = status;
    document.getElementById('editModalNotes').value = notes;

    handleEditStatusChange(status);

    const modal = document.getElementById('editAttendanceModal');
    modal.style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editAttendanceModal').style.display = 'none';
}

function handleEditStatusChange(status) {
    const notesGroup = document.getElementById('editModalNotesGroup');
    const alertBelumAbsen = document.getElementById('editBelumAbsenAlert');

    if (status === 'belum_absen') {
        alertBelumAbsen.style.display = 'block';
        notesGroup.style.display = 'none';
    } else {
        alertBelumAbsen.style.display = 'none';
        notesGroup.style.display = 'block';
    }
}

// ==========================================
// CREATE MODAL LOGIC
// ==========================================
function openCreateModal() {
    document.getElementById('createAttendanceModal').style.display = 'flex';
}

function closeCreateModal() {
    document.getElementById('createAttendanceModal').style.display = 'none';
}

function handleCreateStatusChange(status) {
    const notesGroup = document.getElementById('createModalNotesGroup');
    const alertBelumAbsen = document.getElementById('createBelumAbsenAlert');

    if (status === 'belum_absen') {
        alertBelumAbsen.style.display = 'block';
        notesGroup.style.display = 'none';
    } else {
        alertBelumAbsen.style.display = 'none';
        notesGroup.style.display = 'block';
    }
}

// ==========================================
// RESET MODAL LOGIC
// ==========================================
function openResetModal() {
    const input = document.getElementById('resetConfirmInput');
    input.value = '';
    validateResetButton('');
    document.getElementById('resetAttendanceModal').style.display = 'flex';
}

function closeResetModal() {
    document.getElementById('resetAttendanceModal').style.display = 'none';
}

function validateResetButton(value) {
    const btn = document.getElementById('resetSubmitBtn');
    if (value.trim().toUpperCase() === 'RESET') {
        btn.disabled = false;
        btn.style.opacity = '1';
        btn.style.cursor = 'pointer';
    } else {
        btn.disabled = true;
        btn.style.opacity = '0.6';
        btn.style.cursor = 'not-allowed';
    }
}

// ==========================================
// EXPORT MODAL LOGIC
// ==========================================
function openExportModal() {
    document.getElementById('exportModal').style.display = 'flex';
}

function closeExportModal() {
    document.getElementById('exportModal').style.display = 'none';
}

// ==========================================
// BULK SELECTION LOGIC
// ==========================================
function toggleSelectAll(masterCheckbox) {
    const rowCheckboxes = document.querySelectorAll('.attendance-select-row');
    rowCheckboxes.forEach(cb => {
        cb.checked = masterCheckbox.checked;
    });
    handleRowSelect();
}

function handleRowSelect() {
    const rowCheckboxes = document.querySelectorAll('.attendance-select-row');
    const checked = Array.from(rowCheckboxes).filter(cb => cb.checked);
    const count = checked.length;

    const bar = document.getElementById('bulkActionBar');
    const countEl = document.getElementById('bulkSelectedCount');
    const masterCb = document.getElementById('selectAllCheckbox');

    if (count > 0) {
        bar.style.display = 'flex';
        countEl.innerText = count;
    } else {
        bar.style.display = 'none';
    }

    if (masterCb) {
        masterCb.checked = (rowCheckboxes.length > 0 && count === rowCheckboxes.length);
    }
}

function clearBulkSelection() {
    const rowCheckboxes = document.querySelectorAll('.attendance-select-row');
    rowCheckboxes.forEach(cb => cb.checked = false);
    const masterCb = document.getElementById('selectAllCheckbox');
    if (masterCb) masterCb.checked = false;
    handleRowSelect();
}

function submitBulkDelete() {
    const rowCheckboxes = document.querySelectorAll('.attendance-select-row:checked');
    if (rowCheckboxes.length === 0) return;

    if (!confirm(`Apakah Anda yakin ingin menghapus ${rowCheckboxes.length} data presensi terpilih? Pengguna terpilih akan kembali berstatus Belum Absen.`)) {
        return;
    }

    const container = document.getElementById('bulkDeleteInputsContainer');
    container.innerHTML = '';

    rowCheckboxes.forEach(cb => {
        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'ids[]';
        hidden.value = cb.value;
        container.appendChild(hidden);
    });

    document.getElementById('bulkDeleteForm').submit();
}

// Close modals when clicking outside
window.onclick = function(event) {
    const editModal = document.getElementById('editAttendanceModal');
    const createModal = document.getElementById('createAttendanceModal');
    const resetModal = document.getElementById('resetAttendanceModal');
    const exportModal = document.getElementById('exportModal');

    if (event.target === editModal) closeEditModal();
    if (event.target === createModal) closeCreateModal();
    if (event.target === resetModal) closeResetModal();
    if (event.target === exportModal) closeExportModal();
};
</script>
@endpush
@endsection
