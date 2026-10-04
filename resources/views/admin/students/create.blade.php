@extends('layouts.app')

@section('title', 'Buat Akun Siswa Baru - Musashi Learning')

@section('content')
<div style="max-width: 680px; margin: 0 auto;">
    <div style="margin-bottom: 1.5rem;">
        <a href="{{ route('admin.students.index') }}" class="btn btn-secondary btn-sm" style="margin-bottom: 0.75rem;">
            &larr; Kembali ke Daftar Siswa
        </a>
        <h1 style="font-size: 1.8rem; font-weight: 800;">
            Buat Akun Siswa Baru {{ $isTeacher && $user->subject ? '• ' . $user->subject->name : '' }}
        </h1>
        <p style="color: var(--text-muted); font-size: 0.9rem;">
            Daftarkan siswa secara manual dengan menentukan nama, divisi kerja, dan pilihan kelas belajar {{ $isTeacher && $user->subject ? $user->subject->name : '' }}.
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

            <form action="{{ route('admin.students.store') }}" method="POST">
                @csrf

                @if(!$isTeacher && isset($subjects))
                    <div class="form-group">
                        <label class="form-label" for="subject_id" style="font-weight: 700;">Mata Pelajaran Siswa *</label>
                        <select name="subject_id" id="subject_id" class="form-control" onchange="window.location.href='{{ route('admin.students.create') }}?subject_id=' + this.value;">
                            @foreach($subjects as $s)
                                <option value="{{ $s->id }}" {{ (request('subject_id') == $s->id) ? 'selected' : '' }}>
                                    {{ $s->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 14px; margin-bottom: 1.25rem;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" for="name" style="font-weight: 700;">Nama Lengkap Siswa *</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required placeholder="Contoh: Budi Pratama">
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" for="nrp" style="font-weight: 700;">NRP / NIK Siswa</label>
                        <input type="text" name="nrp" id="nrp" class="form-control" value="{{ old('nrp') }}" placeholder="Contoh: 1367" oninput="handleAdminNrpInput(this.value)">
                        <small style="color: var(--text-muted); font-size: 0.74rem;">Format sandi: <code>[NRP]@musashi</code></small>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 1.25rem;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" for="division" style="font-weight: 700;">Divisi Kerja *</label>
                        <input type="text" name="division" id="division" list="divList" class="form-control" value="{{ old('division') }}" placeholder="Contoh: IT / Produksi" required>
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
                            {{ $isTeacher && $user->subject_id == 2 ? 'Pilihan Grup Bahasa Jepang' : 'Pilihan Kelas ' . ($isTeacher && $user->subject ? $user->subject->name : '') }} *
                        </label>
                        <input type="text" name="class_name" id="class_name" list="classList" class="form-control" value="{{ old('class_name') }}" placeholder="Pilih atau ketik kelas/grup baru" required style="font-weight: 600;">
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
                        <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required placeholder="siswa@musashi.id">
                        <span class="form-text">Digunakan siswa untuk login.</span>
                    </div>

                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" for="phone" style="font-weight: 700;">No. WhatsApp / HP</label>
                        <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone') }}" placeholder="0857xxxxxxxx">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password" style="font-weight: 700; display: flex; justify-content: space-between;">
                        <span>Kata Sandi Awal (Password)</span>
                        <span id="adminPwdBadge" style="color: #0284c7; font-weight: 700; font-size: 0.78rem;">Pola: [NRP]@musashi</span>
                    </label>
                    <input type="text" name="password" id="password" class="form-control" placeholder="Kosongkan untuk otomatis [NRP]@musashi">
                    <small style="color: var(--text-muted); font-size: 0.75rem; display: block; margin-top: 3px;">
                        Jika dikosongkan, kata sandi otomatis diatur seragam ke <code>[NRP]@musashi</code>.
                    </small>
                </div>

                <div class="form-group">
                    <label class="form-label" for="status" style="font-weight: 700;">Status Akun</label>
                    <select name="status" id="status" class="form-control">
                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Aktif (Dapat Mengakses Materi)</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Non-Aktif (Ditangguhkan)</option>
                    </select>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 1.75rem;">
                    <button type="submit" class="btn btn-primary" style="font-weight: 700; padding: 10px 22px;">Daftarkan Akun Siswa</button>
                    <a href="{{ route('admin.students.index') }}" class="btn btn-secondary" style="padding: 10px 18px;">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function handleAdminNrpInput(nrpVal) {
        const clean = nrpVal.replace(/[^a-zA-Z0-9]/g, '');
        const emailInput = document.getElementById('email');
        const pwdInput = document.getElementById('password');
        const badge = document.getElementById('adminPwdBadge');

        if (clean.length > 0) {
            if (badge) badge.innerText = 'Pola: ' + clean + '@musashi';
            if (emailInput && (!emailInput.value || emailInput.value.endsWith('@musashi.co.id') || emailInput.value.endsWith('@musashi.id'))) {
                emailInput.value = clean.toLowerCase() + '@musashi.co.id';
            }
            if (pwdInput && (!pwdInput.value || pwdInput.value.endsWith('@musashi'))) {
                pwdInput.placeholder = clean + '@musashi';
            }
        } else {
            if (badge) badge.innerText = 'Pola: [NRP]@musashi';
        }
    }
</script>
@endsection
