@extends('layouts.app')

@section('title', 'Edit Akun Admin - Musashi Learning')

@section('content')
<div style="max-width: 680px; margin: 0 auto;">
    <div style="margin-bottom: 1.5rem;">
        <a href="{{ route('superadmin.admins.index') }}" class="btn btn-secondary btn-sm" style="margin-bottom: 0.75rem;">
            &larr; Kembali ke Daftar Admin
        </a>
        <h1 style="font-size: 1.8rem;">Edit Akun Admin: {{ $admin->name }}</h1>
        <p style="color: var(--text-muted); font-size: 0.9rem;">
            Perbarui informasi instruktur atau atur ulang kata sandi.
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

            <form action="{{ route('superadmin.admins.update', $admin) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="form-label" for="name">Nama Lengkap Guru / Admin *</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $admin->name) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Alamat Email *</label>
                    <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $admin->email) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="subject_id" style="font-weight: 700;">Mata Pelajaran yang Diajar *</label>
                    <select name="subject_id" id="subject_id" class="form-control" required style="font-weight: 600;">
                        @foreach($subjects as $subj)
                            <option value="{{ $subj->id }}" {{ old('subject_id', $admin->subject_id) == $subj->id ? 'selected' : '' }}>
                                {{ $subj->name }}
                            </option>
                        @endforeach
                    </select>
                    <span class="form-text">Pilihan spesialisasi guru: Bahasa Inggris, Bahasa Jepang, atau Matematika.</span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="phone">Nomor Telepon / WhatsApp</label>
                    <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone', $admin->phone) }}" placeholder="Contoh: 081234567890">
                    <span class="form-text">Nomor telepon dapat digunakan sebagai opsi masuk (login) bagi guru pengajar.</span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Kata Sandi Baru (Opsional)</label>
                    <div style="position: relative;">
                        <input type="password" 
                               name="password" 
                               id="password" 
                               class="form-control" 
                               placeholder="Kosongkan jika tidak ingin mengubah kata sandi"
                               style="padding-right: 42px;">
                        <button type="button" 
                                onclick="toggleEditPasswordVisibility()"
                                style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #64748b; cursor: pointer; padding: 4px;"
                                title="Tampilkan/Sembunyikan sandi">
                            👁️
                        </button>
                    </div>
                    <span class="form-text" style="color: #0284c7; font-weight: 500;">
                        💡 Kata sandi baru yang disimpan akan langsung aktif untuk login pengajar dengan email atau nomor HP tersebut.
                    </span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="status">Status Akun</label>
                    <select name="status" id="status" class="form-control">
                        <option value="active" {{ old('status', $admin->status) === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ old('status', $admin->status) === 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
                    </select>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="{{ route('superadmin.admins.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleEditPasswordVisibility() {
    const input = document.getElementById('password');
    if (input.type === 'password') {
        input.type = 'text';
    } else {
        input.type = 'password';
    }
}
</script>
@endsection
