@extends('layouts.app')

@section('title', 'Kelola Akun Admin (Guru) - Musashi Learning')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.8rem; margin-bottom: 0.25rem;">Kelola Akun Admin (Miss / Guru)</h1>
        <p style="color: var(--text-muted); font-size: 0.92rem;">
            Daftar seluruh instruktur & guru yang berwenang mendaftarkan siswa dan mengelola modul belajar.
        </p>
    </div>
    <a href="{{ route('superadmin.admins.create') }}" class="btn btn-primary">
        + Buat Akun Admin Baru
    </a>
</div>

<!-- Search & Filter Card -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body" style="padding: 1rem 1.5rem;">
        <form method="GET" action="{{ route('superadmin.admins.index') }}" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 240px;">
                <input type="text" name="search" class="form-control" placeholder="Cari nama, email, atau telepon..." value="{{ request('search') }}">
            </div>
            <div>
                <select name="status" class="form-control">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
                </select>
            </div>
            <button type="submit" class="btn btn-secondary">Filter</button>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('superadmin.admins.index') }}" class="btn btn-secondary" style="color: var(--text-muted);">Reset</a>
            @endif
        </form>
    </div>
</div>

<!-- Admins Table Card -->
<div class="card">
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Admin / Guru</th>
                        <th>Mata Pelajaran (1 Matkul)</th>
                        <th>Siswa Terdaftar</th>
                        <th>Email & Kontak</th>
                        <th>Status</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($admins as $index => $admin)
                        <tr>
                            <td>{{ $admins->firstItem() + $index }}</td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div class="avatar" style="width: 34px; height: 34px; font-size: 0.85rem;">
                                        {{ strtoupper(substr($admin->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <strong>{{ $admin->name }}</strong>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);">ID: #{{ $admin->id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($admin->subject)
                                    <span class="badge" style="background: {{ $admin->subject->badge_color }}15; color: {{ $admin->subject->badge_color }}; font-weight: 700;">
                                        {{ $admin->subject->name }}
                                    </span>
                                @else
                                    <span class="badge badge-warning">Belum Ditugaskan</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-primary">
                                    {{ $admin->teacher_enrollments_count }} Siswa Aktif
                                </span>
                            </td>
                            <td>
                                <div style="font-size: 0.88rem; color: #0f172a;">{{ $admin->email }}</div>
                                @if($admin->phone)
                                    <div style="font-size: 0.78rem; color: var(--text-muted);">📞 {{ $admin->phone }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $admin->status === 'active' ? 'badge-success' : 'badge-danger' }}">
                                    {{ $admin->status === 'active' ? 'Aktif' : 'Non-Aktif' }}
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 6px;">
                                    <a href="{{ route('superadmin.admins.edit', $admin) }}" class="btn btn-secondary btn-sm">
                                        Edit
                                    </a>
                                    <form action="{{ route('superadmin.admins.destroy', $admin) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun admin ini?')">
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
                            <td colspan="7" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                                Tidak ditemukan data akun admin.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($admins->hasPages())
        <div class="card-footer">
            {{ $admins->links() }}
        </div>
    @endif
</div>
@endsection
