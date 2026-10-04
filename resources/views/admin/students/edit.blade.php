@extends('layouts.app')

@section('title', 'Edit Akun Siswa - Musashi Learning')

@section('content')
<div style="max-width: 680px; margin: 0 auto;">
    <div style="margin-bottom: 1.5rem;">
        <a href="{{ route('admin.students.index') }}" class="btn btn-secondary btn-sm" style="margin-bottom: 0.75rem;">
            &larr; Kembali ke Daftar Siswa
        </a>
        <h1 style="font-size: 1.8rem; font-weight: 800;">Edit Akun Siswa: {{ $student->name }}</h1>
        <p style="color: var(--text-muted); font-size: 0.9rem;">
            Perbarui data profil siswa, divisi kerja, pilihan kelas Bahasa Inggris, atau atur ulang kata sandi.
        </p>
    </div>

    <div class="card" style="box-shadow: var(--shadow-sm);">
        <div class="card-body" style="padding: 2rem;">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul style="margin-left: 1.25rem;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.students.update', $student) }}" method="POST">
                @csrf
                @method('PUT')

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 14px; margin-bottom: 1.25rem;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" for="name" style="font-weight: 700;">Nama Lengkap Siswa *</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $student->name) }}" required>
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" for="nrp" style="font-weight: 700;">NRP / NIK Siswa</label>
                        <input type="text" name="nrp" id="nrp" class="form-control" value="{{ old('nrp', $student->nrp) }}" placeholder="Contoh: 1367" oninput="handleAdminEditNrp(this.value)">
                        <small style="color: var(--text-muted); font-size: 0.74rem;">Pola sandi: <code>[NRP]@musashi</code></small>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 1.25rem;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" for="division" style="font-weight: 700;">Divisi Kerja</label>
                        <input type="text" name="division" id="division" list="divList" class="form-control" value="{{ old('division', $student->division) }}" placeholder="Contoh: IT / Produksi">
                        <datalist id="divList">
                            <option value="IT / Software">
                            <option value="Produksi / Operasional">
                            <option value="Quality Control (QC)">
                            <option value="Human Resources (HRD)">
                            <option value="Keuangan / Finance">
                            <option value="Purchasing">
                            <option value="Logistik / Gudang">
                            <option value="Maintenance / Engineering">
                        </datalist>
                    </div>

                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" for="class_name" style="font-weight: 700;">
                            {{ $student->subject_id == 2 ? 'Pilihan Grup Bahasa Jepang' : 'Pilihan Kelas ' . ($student->subject_id == 3 ? 'Matematika' : 'Bahasa Inggris') }}
                        </label>
                        <input type="text" name="class_name" id="class_name" list="classList" class="form-control" value="{{ old('class_name', $student->class_name) }}" placeholder="Pilih atau ketik kelas/grup baru" style="font-weight: 600;">
                        <datalist id="classList">
                            @if(isset($classes))
                                @foreach($classes as $cls)
                                    <option value="{{ $cls->name }}">{{ $cls->name }}</option>
                                @endforeach
                            @endif
                        </datalist>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 1.25rem;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" for="email" style="font-weight: 700;">Alamat Email Siswa *</label>
                        <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $student->email) }}" required>
                    </div>

                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" for="phone" style="font-weight: 700;">Nomor Telepon / WhatsApp</label>
                        <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone', $student->phone) }}">
                    </div>
                </div>

                @php
                    $cleanNrp = $student->nrp ? preg_replace('/[^a-zA-Z0-9]/', '', $student->nrp) : '';
                    $patternPwd = $cleanNrp ? "{$cleanNrp}@musashi" : 'password';
                @endphp
                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                        <label class="form-label" for="password" style="font-weight: 700; margin: 0;">
                            Kata Sandi Baru (Opsional)
                        </label>
                        <button type="button" 
                                id="btnAdminResetPwd"
                                class="btn btn-secondary btn-sm" 
                                onclick="adminResetPatternPwd()"
                                style="padding: 2px 7px; font-size: 0.72rem; font-weight: 700; color: #166534; background: #dcfce7; border-color: #86efac;">
                            🔄 Setel ke {{ $patternPwd }}
                        </button>
                    </div>
                    <input type="text" name="password" id="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah kata sandi">
                    <span class="form-text">Biarkan kosong jika kata sandi siswa tidak diubah.</span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="status" style="font-weight: 700;">Status Akun</label>
                    <select name="status" id="status" class="form-control">
                        <option value="active" {{ old('status', $student->status) === 'active' ? 'selected' : '' }}>Aktif (Dapat Mengakses Materi)</option>
                        <option value="inactive" {{ old('status', $student->status) === 'inactive' ? 'selected' : '' }}>Non-Aktif (Ditangguhkan)</option>
                    </select>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 1.75rem;">
                    <button type="submit" class="btn btn-primary" style="font-weight: 700; padding: 10px 22px;">Simpan Perubahan</button>
                    <a href="{{ route('admin.students.index') }}" class="btn btn-secondary" style="padding: 10px 18px;">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let adminPatternPwd = '{{ $patternPwd }}';

    function handleAdminEditNrp(val) {
        const clean = val.replace(/[^a-zA-Z0-9]/g, '');
        adminPatternPwd = clean.length > 0 ? (clean + '@musashi') : 'password';
        const btn = document.getElementById('btnAdminResetPwd');
        if (btn) btn.innerText = '🔄 Setel ke ' + adminPatternPwd;
    }

    function adminResetPatternPwd() {
        const input = document.getElementById('password');
        if (input) {
            input.value = adminPatternPwd;
            alert('Kata sandi diset ke: ' + adminPatternPwd + '.\nKlik Simpan Perubahan untuk menyimpan.');
        }
    }
</script>
@endsection
