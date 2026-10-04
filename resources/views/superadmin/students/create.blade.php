@extends('layouts.app')

@section('title', 'Tambah Siswa Manual - Super Administrator Musashi')

@section('content')
<div style="max-width: 760px; margin: 0 auto;">
    <div style="margin-bottom: 1.5rem;">
        <a href="{{ route('superadmin.students.index', ['subject_id' => $currentSubject->id]) }}" class="btn btn-secondary btn-sm" style="margin-bottom: 0.75rem;">
            &larr; Kembali ke Kelola Siswa
        </a>
        <h1 style="font-size: 1.8rem; font-weight: 800; color: #0f172a;">
            Tambah Akun Siswa Baru (Manual)
        </h1>
        <p style="color: var(--text-muted); font-size: 0.92rem;">
            Daftarkan peserta didik baru untuk mata pelajaran <strong>{{ $currentSubject->name }}</strong> atau mata pelajaran lainnya.
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

            <form action="{{ route('superadmin.students.store') }}" method="POST">
                @csrf

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 1.25rem;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" for="subject_id" style="font-weight: 700;">
                            Mata Pelajaran *
                        </label>
                        <select name="subject_id" id="subject_id" class="form-control" required style="font-weight: 700;" onchange="window.location.href='{{ route('superadmin.students.create') }}?subject_id=' + this.value">
                            @foreach($subjects as $subj)
                                <option value="{{ $subj->id }}" {{ (int)old('subject_id', $currentSubject->id) === $subj->id ? 'selected' : '' }}>
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
                               value="{{ old('class_name') }}" 
                               placeholder="Pilih dari daftar atau ketik baru (contoh: {{ $currentSubject->id == 2 ? 'Grup 1' : 'I1' }})"
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



                @if($currentSubject->id === 1)
                    <div class="form-group" style="margin-bottom: 1.25rem; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: var(--radius-md); padding: 14px 18px;">
                        <label class="form-label" for="teacher_id" style="font-weight: 700; color: #1e3a8a; margin-bottom: 4px;">
                            Guru Pengajar (Khusus Bahasa Inggris)
                        </label>
                        <select name="teacher_id" id="teacher_id" class="form-control" style="font-weight: 600; background: #ffffff; border-color: #93c5fd;">
                            <option value="">-- Pilih Guru Pengajar --</option>
                            @foreach($englishTeachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ (string)old('teacher_id') === (string)$teacher->id ? 'selected' : '' }}>
                                    👨‍🏫 {{ $teacher->name }} ({{ $teacher->email }})
                                </option>
                            @endforeach
                        </select>
                        <small style="color: #475569; font-size: 0.74rem; display: block; margin-top: 4px;">
                            Pilih guru yang mengajar murid ini. Setiap murid baru Bahasa Inggris dapat diajar oleh guru yang berbeda sesuai pembagian kelas.
                        </small>
                    </div>
                @endif
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" for="name" style="font-weight: 700;">Nama Lengkap Siswa *</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required placeholder="Contoh: FERIANSYAH">
                    </div>

                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" for="nrp" style="font-weight: 700;">NRP / NIK Siswa</label>
                        <input type="text" 
                               name="nrp" 
                               id="nrp" 
                               class="form-control" 
                               value="{{ old('nrp') }}" 
                               placeholder="Contoh: 1367"
                               oninput="handleNrpInput(this.value)">
                        <small style="color: var(--text-muted); font-size: 0.74rem; display: block; margin-top: 3px;">
                            Menentukan pola sandi: <code>[NRP]@musashi</code>
                        </small>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 1.25rem;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" for="division" style="font-weight: 700;">Bagian / Divisi / Departemen</label>
                        <input type="text" name="division" id="division" class="form-control" value="{{ old('division') }}" placeholder="Contoh: INFORMATION TECHNOLOGY">
                    </div>

                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" for="phone" style="font-weight: 700;">Nomor Telepon / WhatsApp</label>
                        <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone') }}" placeholder="Contoh: 081234567890">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 1.25rem;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" for="email" style="font-weight: 700;">Alamat Email Siswa</label>
                        <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" placeholder="Otomatis: [nrp]@musashi.id">
                        <small style="color: var(--text-muted); font-size: 0.74rem; display: block; margin-top: 3px;">
                            Jika dikosongkan, email otomatis: <code>[nrp]@musashi.id</code>.
                        </small>
                    </div>

                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" for="password" style="font-weight: 700; display: flex; justify-content: space-between;">
                            <span>Kata Sandi Akun</span>
                            <span id="pwdPatternBadge" style="color: #0284c7; font-weight: 700; font-size: 0.76rem;">Default: [NRP]@musashi</span>
                        </label>
                        <div style="position: relative;">
                            <input type="text" 
                                   name="password" 
                                   id="password" 
                                   class="form-control" 
                                   value="{{ old('password') }}" 
                                   placeholder="Otomatis [NRP]@musashi jika kosong">
                        </div>
                        <small style="color: var(--text-muted); font-size: 0.74rem; display: block; margin-top: 3px;">
                            Default sandi disamakan ke <code>[NRP]@musashi</code> (contoh: <code>00001@musashi</code>).
                        </small>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label class="form-label" for="status" style="font-weight: 700;">Status Akun Siswa *</label>
                    <select name="status" id="status" class="form-control" required style="width: 200px; font-weight: 700;">
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>🟢 Aktif (Dapat Login)</option>
                        <option value="inactive" {{ old('status', 'inactive') === 'inactive' ? 'selected' : '' }}>🔴 Non-Aktif (Terkunci)</option>
                    </select>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary" style="font-weight: 700; padding: 10px 24px;">
                        Simpan Akun Siswa
                    </button>
                    <a href="{{ route('superadmin.students.index', ['subject_id' => $currentSubject->id]) }}" class="btn btn-secondary" style="padding: 10px 20px;">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function handleNrpInput(nrpVal) {
        const clean = nrpVal.replace(/[^a-zA-Z0-9]/g, '');
        const emailInput = document.getElementById('email');
        const pwdInput = document.getElementById('password');
        const badge = document.getElementById('pwdPatternBadge');

        if (clean.length > 0) {
            if (badge) badge.innerText = 'Pola: ' + clean + '@musashi';
            if (emailInput && (!emailInput.value || emailInput.value.endsWith('@musashi.co.id') || emailInput.value.endsWith('@musashi.id'))) {
                emailInput.placeholder = clean.toLowerCase() + '@musashi.co.id';
            }
            if (pwdInput && (!pwdInput.value || pwdInput.value.endsWith('@musashi'))) {
                pwdInput.placeholder = clean + '@musashi';
            }
        } else {
            if (badge) badge.innerText = 'Default: [NRP]@musashi';
        }
    }
</script>
@endsection
