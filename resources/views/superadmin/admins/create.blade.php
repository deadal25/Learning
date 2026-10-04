@extends('layouts.app')

@section('title', 'Tambah Akun Admin Baru - Musashi Learning')

@section('content')
<div style="max-width: 680px; margin: 0 auto;">
    <div style="margin-bottom: 1.5rem;">
        <a href="{{ route('superadmin.admins.index') }}" class="btn btn-secondary btn-sm" style="margin-bottom: 0.75rem;">
            &larr; Kembali ke Daftar Admin
        </a>
        <h1 style="font-size: 1.8rem;">Buat Akun Admin / Guru Baru</h1>
        <p style="color: var(--text-muted); font-size: 0.9rem;">
            Akun ini akan memiliki akses untuk mendaftarkan akun siswa dan mengelola modul materi serta latihan soal.
        </p>
    </div>

    <div class="card">
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul style="margin-left: 1.25rem;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('superadmin.admins.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="name">Nama Lengkap Guru / Admin *</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required placeholder="Contoh: Miss Sarah Jenkins">
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Alamat Email *</label>
                    <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required placeholder="sarah@musashi.id">
                    <span class="form-text">Email ini digunakan untuk login ke Learning Musashi admin.</span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="subject_id" style="font-weight: 700;">Mata Pelajaran yang Diajar *</label>
                    <select name="subject_id" id="subject_id" class="form-control" required style="font-weight: 600;">
                        <option value="">-- Pilih Mata Pelajaran (Bahasa Inggris, Bahasa Jepang, Matematika) --</option>
                        @foreach($subjects as $subj)
                            <option value="{{ $subj->id }}" {{ old('subject_id') == $subj->id ? 'selected' : '' }}>
                                {{ $subj->name }}
                            </option>
                        @endforeach
                    </select>
                    <span class="form-text">Pilihan spesialisasi guru: Bahasa Inggris, Bahasa Jepang, atau Matematika.</span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="phone">Nomor Telepon / WhatsApp</label>
                    <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone') }}" placeholder="081234567890">
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Kata Sandi (Password) *</label>
                    <input type="password" name="password" id="password" class="form-control" required placeholder="Minimal 6 karakter">
                </div>

                <div class="form-group">
                    <label class="form-label" for="status">Status Akun</label>
                    <select name="status" id="status" class="form-control">
                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
                    </select>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary">Simpan Akun Admin</button>
                    <a href="{{ route('superadmin.admins.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
