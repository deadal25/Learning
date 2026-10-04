@extends('layouts.app')

@section('title', 'Edit Data Siswa - Super Administrator Musashi')

@section('content')
<div style="max-width: 760px; margin: 0 auto;">
    <div style="margin-bottom: 1.5rem;">
        <a href="{{ route('superadmin.students.index', ['subject_id' => $student->subject_id ?? 1]) }}" class="btn btn-secondary btn-sm" style="margin-bottom: 0.75rem;">
            &larr; Kembali ke Kelola Siswa
        </a>
        <h1 style="font-size: 1.8rem; font-weight: 800; color: #0f172a;">
            Edit Siswa: {{ $student->name }}
        </h1>
        <p style="color: var(--text-muted); font-size: 0.92rem;">
            Perbarui data identitas, kelas, guru pengajar, mata pelajaran, atau ubah kata sandi akun siswa.
        </p>
    </div>

    <div class="card" style="box-shadow: var(--shadow-sm);">
        <div class="card-body" style="padding: 2rem;">
            @if($errors->any())
                <div class="alert alert-danger" style="margin-bottom: 1.5rem;">
                    <ul style="margin-left: 1.25rem;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('superadmin.students.update', $student) }}" method="POST">
                @csrf
                @method('PUT')

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 1.25rem;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" for="subject_id" style="font-weight: 700;">
                            Mata Pelajaran *
                        </label>
                        <select name="subject_id" id="subject_id" class="form-control" required style="font-weight: 700;">
                            @foreach($subjects as $subj)
                                <option value="{{ $subj->id }}" {{ (int)old('subject_id', $student->subject_id) === $subj->id ? 'selected' : '' }}>
                                    {{ $subj->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" for="class_name" style="font-weight: 700;">
                            Nama Kelas / Nama Grup
                        </label>
                        <input type="text" 
                               name="class_name" 
                               id="class_name" 
                               list="classList" 
                               class="form-control" 
                               value="{{ old('class_name', $student->class_name) }}" 
                               placeholder="Pilih dari daftar atau ketik baru (contoh: Grup 1, I1, dll)"
                               style="font-weight: 600;">
                        <datalist id="classList">
                            @foreach($classes as $cls)
                                <option value="{{ $cls->name }}">{{ $cls->name }}</option>
                            @endforeach
                        </datalist>
                        <small style="color: var(--text-muted); font-size: 0.74rem; display: block; margin-top: 3px;">
                            Bisa memilih kelas yang sudah ada atau mengetikkan nama kelas / grup baru.
                        </small>
                    </div>
                </div>
                <div id="teacher_assignment_section" style="margin-bottom: 1.25rem; {{ (int)old('subject_id', $student->subject_id ?? 1) === 1 ? '' : 'display: none;' }}">
                    <div class="form-group" style="margin: 0; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: var(--radius-md); padding: 14px 18px;">
                        <label class="form-label" for="teacher_id" style="font-weight: 700; color: #1e3a8a; margin-bottom: 4px;">
                            Guru Pengajar (Khusus Bahasa Inggris)
                        </label>
                        <select name="teacher_id" id="teacher_id" class="form-control" style="font-weight: 600; background: #ffffff; border-color: #93c5fd;">
                            <option value="">-- Pilih Guru Pengajar --</option>
                            @foreach($englishTeachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ (string)old('teacher_id', $currentTeacherId) === (string)$teacher->id ? 'selected' : '' }}>
                                    👨‍🏫 {{ $teacher->name }} ({{ $teacher->email }})
                                </option>
                            @endforeach
                        </select>
                        <small style="color: #475569; font-size: 0.74rem; display: block; margin-top: 4px;">
                            Pilih guru yang mengajar siswa ini. Murid dapat dipindahkan atau ditugaskan ke guru yang berbeda sesuai kebutuhan.
                        </small>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 1.25rem;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" for="name" style="font-weight: 700;">Nama Lengkap Siswa *</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $student->name) }}" required>
                    </div>

                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" for="nrp" style="font-weight: 700;">NRP / NIK Siswa</label>
                        <input type="text" 
                               name="nrp" 
                               id="nrp" 
                               class="form-control" 
                               value="{{ old('nrp', $student->nrp) }}" 
                               placeholder="Contoh: 1367"
                               oninput="handleEditNrpInput(this.value)">
                        <small style="color: var(--text-muted); font-size: 0.74rem; display: block; margin-top: 3px;">
                            Pola sandi bawaan: <code>[NRP]@musashi</code>
                        </small>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 1.25rem;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" for="division" style="font-weight: 700;">Bagian / Divisi / Departemen</label>
                        <input type="text" name="division" id="division" class="form-control" value="{{ old('division', $student->division) }}" placeholder="Contoh: INFORMATION TECHNOLOGY">
                    </div>

                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" for="phone" style="font-weight: 700;">Nomor Telepon / WhatsApp</label>
                        <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone', $student->phone) }}">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 1.25rem;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" for="email" style="font-weight: 700;">Alamat Email Siswa *</label>
                        <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $student->email) }}" required>
                    </div>

                    @php
                        $cleanNrp = $student->nrp ? preg_replace('/[^a-zA-Z0-9]/', '', $student->nrp) : '';
                        $patternPwd = $cleanNrp ? "{$cleanNrp}@musashi" : 'password';
                    @endphp
                    <div class="form-group" style="margin: 0;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                            <label class="form-label" for="password" style="font-weight: 700; margin: 0;">
                                Ganti Kata Sandi (Opsional)
                            </label>
                            <button type="button" 
                                    id="btnResetPattern"
                                    class="btn btn-secondary btn-sm" 
                                    onclick="resetToPatternPassword()"
                                    style="padding: 2px 7px; font-size: 0.72rem; font-weight: 700; color: #166534; background: #dcfce7; border-color: #86efac;"
                                    title="Isi otomatis dengan pola [NRP]@musashi">
                                🔄 Setel ke {{ $patternPwd }}
                            </button>
                        </div>
                        <div style="position: relative;">
                            <input type="text" 
                                   name="password" 
                                   id="password" 
                                   class="form-control" 
                                   placeholder="Kosongkan jika tidak ingin diubah">
                        </div>
                        <small style="color: var(--text-muted); font-size: 0.74rem; display: block; margin-top: 3px;">
                            Biarkan kosong jika tidak diubah, atau ketik kata sandi baru / klik tombol reset di atas.
                        </small>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label class="form-label" for="status" style="font-weight: 700;">Status Akun Siswa *</label>
                    <select name="status" id="status" class="form-control" required style="width: 200px; font-weight: 700;">
                        <option value="active" {{ old('status', $student->status) === 'active' ? 'selected' : '' }}>🟢 Aktif (Dapat Login)</option>
                        <option value="inactive" {{ old('status', $student->status) === 'inactive' ? 'selected' : '' }}>🔴 Non-Aktif (Terkunci)</option>
                    </select>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary" style="font-weight: 700; padding: 10px 24px;">
                        Simpan Perubahan
                    </button>
                    <a href="{{ route('superadmin.students.index', ['subject_id' => $student->subject_id ?? 1]) }}" class="btn btn-secondary" style="padding: 10px 20px;">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let currentSuggestedPattern = '{{ $patternPwd }}';

    function handleEditNrpInput(nrpVal) {
        const clean = nrpVal.replace(/[^a-zA-Z0-9]/g, '');
        currentSuggestedPattern = clean.length > 0 ? (clean + '@musashi') : 'password';
        const btnReset = document.getElementById('btnResetPattern');
        if (btnReset) {
            btnReset.innerText = '🔄 Setel ke ' + currentSuggestedPattern;
        }
    }

    function resetToPatternPassword() {
        const pwdInput = document.getElementById('password');
        if (pwdInput) {
            pwdInput.value = currentSuggestedPattern;
            alert('Kata sandi baru diisi dengan: ' + currentSuggestedPattern + '.\nKlik "Simpan Perubahan" untuk menerapkan.');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const subjSelect = document.getElementById('subject_id');
        const teacherSec = document.getElementById('teacher_assignment_section');
        if (subjSelect && teacherSec) {
            subjSelect.addEventListener('change', function() {
                teacherSec.style.display = (this.value == '1') ? 'block' : 'none';
            });
        }
    });
</script>
@endsection
