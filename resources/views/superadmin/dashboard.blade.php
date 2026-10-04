@extends('layouts.app')

@section('title', 'Dashboard Super Admin - Musashi Learning')

@section('content')
<div class="hero-banner">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div class="badge badge-warning" style="margin-bottom: 0.75rem;">Learning Musashi Super Administrator</div>
            <h1 style="font-size: 2rem; margin-bottom: 0.5rem;">Selamat Datang, {{ auth()->user()->name }}!</h1>
            <p style="opacity: 0.9; max-width: 600px; font-size: 0.95rem;">
                Anda memiliki kendali penuh atas manajemen akun Admin (Miss/Guru), pemantauan statistik platform, dan hak akses menyeluruh.
            </p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('superadmin.students.index') }}" class="btn btn-primary" style="font-weight: 700;">
                👥 Kelola Siswa & Import Excel
            </a>
            <a href="{{ route('superadmin.admins.create') }}" class="btn btn-warning">
                + Tambah Akun Guru
            </a>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary" style="background: rgba(255,255,255,0.15); color: #fff; border-color: rgba(255,255,255,0.3);">
                Mode Belajar &rarr;
            </a>
        </div>
    </div>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-4" style="margin-bottom: 2rem;">
    <div class="stat-card">
        <div class="stat-icon" style="background: #e0e7ff; color: #4338ca;">👩‍🏫</div>
        <div>
            <div class="stat-val">{{ $stats['total_admins'] }}</div>
            <div class="stat-label">Total Admin / Guru</div>
        </div>
    </div>
    <a href="{{ route('superadmin.students.index') }}" class="stat-card" style="text-decoration: none; color: inherit; cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;">
        <div class="stat-icon" style="background: #d1fae5; color: #065f46;">🎒</div>
        <div>
            <div class="stat-val">{{ $stats['total_students'] }}</div>
            <div class="stat-label" style="display: flex; align-items: center; gap: 4px;">
                <span>Total Pelajar / Siswa</span>
                <span style="font-size: 0.75rem; color: #059669; font-weight: 700;">&rarr; Kelola</span>
            </div>
        </div>
    </a>
    <div class="stat-card">
        <div class="stat-icon" style="background: #fef3c7; color: #92400e;">📚</div>
        <div>
            <div class="stat-val">{{ $stats['total_subjects'] }}</div>
            <div class="stat-label">Mata Pelajaran Aktif</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #ffe4e6; color: #9f1239;">📑</div>
        <div>
            <div class="stat-val">{{ $stats['total_materials'] }}</div>
            <div class="stat-label">Materi Slide Terunggah</div>
        </div>
    </div>
</div>

<div class="grid grid-cols-2">
    <!-- Recent Admins -->
    <div class="card">
        <div class="card-header">
            <h3 style="font-size: 1.15rem;">Daftar Akun Admin / Guru Terbaru</h3>
            <a href="{{ route('superadmin.admins.index') }}" class="btn btn-secondary btn-sm">Lihat Semua</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama Guru</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentAdmins as $admin)
                            <tr>
                                <td>
                                    <strong>{{ $admin->name }}</strong>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $admin->phone ?? '-' }}</div>
                                </td>
                                <td>{{ $admin->email }}</td>
                                <td>
                                    <span class="badge {{ $admin->status === 'active' ? 'badge-success' : 'badge-danger' }}">
                                        {{ $admin->status === 'active' ? 'Aktif' : 'Non-Aktif' }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('superadmin.admins.edit', $admin) }}" class="btn btn-secondary btn-sm">
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                                    Belum ada akun admin terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Students -->
    <div class="card">
        <div class="card-header">
            <h3 style="font-size: 1.15rem;">Siswa Terdaftar Terbaru</h3>
            <a href="{{ route('superadmin.students.index') }}" class="btn btn-secondary btn-sm">Kelola Siswa</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama Siswa</th>
                            <th>Email Akun</th>
                            <th>Kelas / Grup</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentStudents as $student)
                            <tr>
                                <td>
                                    <strong>{{ $student->name }}</strong>
                                    @if($student->nrp)
                                        <div style="font-size: 0.74rem; color: #475569; font-weight: 700; font-family: monospace;">
                                            NRP: {{ $student->nrp }}
                                        </div>
                                    @endif
                                </td>
                                <td>{{ $student->email }}</td>
                                <td>
                                    <span class="badge badge-primary">
                                        {{ $student->class_name ?: 'Reguler' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                                    Belum ada siswa terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
