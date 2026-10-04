@extends('layouts.app')

@section('title', 'Riwayat Absensi Guru & Siswa - Super Admin - Musashi')

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

    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px; flex-wrap: wrap;">
                <span class="badge" style="background: #eef2ff; color: #4338ca; font-weight: 800; font-size: 0.8rem; border: 1px solid #c7d2fe;">
                    Learning Musashi Super Admin
                </span>
                <span class="badge" style="background: #eff6ff; color: #1e40af; font-weight: 800; font-size: 0.8rem; border: 1px solid #bfdbfe;">
                    {{ $currentSubject->name }}
                </span>
            </div>
            <h1 style="font-size: 1.85rem; margin: 0; font-weight: 800; color: #0f172a;">
                ⏱️ Riwayat Absensi: {{ $currentSubject->name }}
            </h1>
            <p style="color: var(--text-muted); font-size: 0.92rem; margin-top: 4px;">
                Pantau rekaman jam presensi kehadiran guru dan siswa untuk mata pelajaran <strong>{{ $currentSubject->name }}</strong>.
            </p>
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
                <input type="text" name="search" class="form-control" placeholder="Cari nama atau email..." value="{{ $search }}">
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

<!-- Attendance Table -->
<div class="card" style="box-shadow: var(--shadow-sm); border-radius: var(--radius-lg); overflow: hidden;">
    <div class="card-header" style="background: #ffffff; border-bottom: 1px solid var(--border-color); padding: 1.15rem 1.5rem;">
        <h2 style="font-size: 1.15rem; margin: 0; font-weight: 800; color: #0f172a;">
            📋 Rekap {{ $activeTab === 'guru' ? 'Absensi Guru' : 'Absensi Siswa' }} ({{ $currentSubject->name }})
        </h2>
    </div>

    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table" style="margin: 0;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                        <th style="width: 60px; text-align: center;">No</th>
                        <th style="width: 160px;">Tanggal & Waktu</th>
                        <th>{{ $activeTab === 'guru' ? 'Nama Pengajar' : 'Nama Siswa' }}</th>
                        @if($activeTab === 'siswa')
                            <th>Kelas / Grup & Divisi</th>
                        @endif
                        <th style="width: 140px; text-align: center;">Status Presensi</th>
                        <th>Catatan / Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $index => $att)
                        <tr style="border-bottom: 1px solid #f1f5f9;">
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
                                        {{ strtoupper(substr($att->user->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; color: #0f172a; font-size: 0.92rem;">
                                            {{ $att->user->name ?? 'Pengguna Tidak Diketahui' }}
                                        </div>
                                        <div style="font-size: 0.74rem; color: #64748b;">
                                            {{ $att->user->email ?? '-' }}
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
                                    <span class="badge" style="background: #ecfdf5; color: #065f46; font-weight: 800; font-size: 0.76rem; border: 1px solid #a7f3d0;">
                                        ✅ Hadir
                                    </span>
                                @elseif($att->status === 'izin_keterangan')
                                    <span class="badge" style="background: #eff6ff; color: #1e40af; font-weight: 700; font-size: 0.76rem; border: 1px solid #bfdbfe;">
                                        📝 Izin (Keterangan)
                                    </span>
                                @else
                                    <span class="badge" style="background: #fef2f2; color: #991b1b; font-weight: 700; font-size: 0.76rem; border: 1px solid #fecaca;">
                                        ⚠️ Izin (Tanpa Ket.)
                                    </span>
                                @endif
                            </td>
                            <td style="vertical-align: middle; color: #334155; font-size: 0.85rem;">
                                {{ $att->notes ?: '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $activeTab === 'siswa' ? 6 : 5 }}" style="text-align: center; padding: 3rem 1.5rem; color: var(--text-muted);">
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
@endsection
