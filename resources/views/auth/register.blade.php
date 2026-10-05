@extends('layouts.app')

@section('title', 'Pendaftaran Siswa - Learning Musashi')

@section('content')
<div style="max-width: 600px; margin: 1.5rem auto 3rem;">
    <div class="card" style="box-shadow: var(--shadow-lg); border-radius: var(--radius-xl); border: 1px solid #e2e8f0;">
        <div class="card-body" style="padding: 2.25rem 2.5rem;">
            <div style="text-align: center; margin-bottom: 1.75rem;">
                <div style="display: flex; align-items: center; justify-content: center; gap: 14px; margin-bottom: 1rem;">
                    <img src="{{ asset('images/bola.png') }}" alt="Logo" style="height: 48px; width: auto; object-fit: contain;">
                    <img src="{{ asset('images/Gambar1.png') }}" alt="MUSASHI" style="height: 36px; width: auto; object-fit: contain;">
                </div>
                <h1 style="font-size: 1.65rem; color: #0f172a; margin-bottom: 0.35rem; font-weight: 800;">
                    Pendaftaran Akun Siswa Baru
                </h1>
                <p style="font-size: 0.9rem; color: var(--text-muted); line-height: 1.5;">
                    Pilih mata pelajaran yang ingin dipelajari, isi data divisi kerja, dan pilih kelas Anda untuk langsung mengakses materi dan presensi harian.
                </p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger" style="margin-bottom: 1.5rem;">
                    <ul style="margin: 0; padding-left: 1.25rem;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('register.post') }}" method="POST">
                @csrf

                <!-- Pilihan Mata Pelajaran (Program Belajar) -->
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label class="form-label" style="font-weight: 700; color: #1e293b; display: block; margin-bottom: 8px;">
                        Pilih Program / Mata Pelajaran Belajar <span style="color: #ef4444;">*</span>
                    </label>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
                        @foreach($subjects as $subj)
                            @php
                                $emoji = match($subj->id) {
                                    1 => '📚',
                                    2 => '🌸',
                                    3 => '🔢',
                                    default => '📖',
                                };
                                $subText = match($subj->id) {
                                    1 => '25 Pertemuan',
                                    2 => '12 Pertemuan (Grup 1 - 12)',
                                    3 => 'Kalkulasi Industri',
                                    default => 'Aktif',
                                };
                                $isSelected = old('subject_id', 1) == $subj->id;
                            @endphp
                            <label id="label_subj_{{ $subj->id }}" style="border: 2px solid {{ $isSelected ? '#2563eb' : '#e2e8f0' }}; background: {{ $isSelected ? '#eff6ff' : '#ffffff' }}; border-radius: var(--radius-md); padding: 10px 8px; text-align: center; cursor: pointer; transition: all 0.2s;">
                                <input type="radio" name="subject_id" value="{{ $subj->id }}" {{ $isSelected ? 'checked' : '' }} onchange="onSubjectChange({{ $subj->id }})" style="display: none;">
                                <div style="font-size: 1.6rem; line-height: 1.1;">{{ $emoji }}</div>
                                <div style="font-weight: 800; font-size: 0.88rem; color: #0f172a; margin-top: 4px;">
                                    {{ $subj->name }}
                                </div>
                                <div style="font-size: 0.72rem; color: #64748b; margin-top: 2px;">
                                    {{ $subText }}
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Nama Lengkap Siswa -->
                <div class="form-group" style="margin-bottom: 1.15rem;">
                    <label class="form-label" for="name" style="font-weight: 700; color: #1e293b;">
                        Nama Lengkap Siswa <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required autofocus placeholder="Contoh: Budi Santoso" style="padding: 10px 14px;">
                </div>

                <!-- Divisi & Pilihan Kelas (2 Kolom) -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 1.15rem;">
                    <!-- Divisi -->
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" for="division" style="font-weight: 700; color: #1e293b;">
                            Divisi Kerja <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="text" name="division" id="division" list="divisionList" class="form-control" value="{{ old('division') }}" required placeholder="Contoh: Produksi / IT" style="padding: 10px 14px;">
                        <datalist id="divisionList">
                            <option value="Produksi Engine">
                            <option value="Quality Control (QC)">
                            <option value="IT / Software">
                            <option value="Machining">
                            <option value="Assembly">
                            <option value="Logistik / Gudang">
                            <option value="Maintenance / Engineering">
                            <option value="Human Resources (HRD)">
                            <option value="Keuangan / Finance">
                            <option value="Marketing / Sales">
                        </datalist>
                        <small style="font-size: 0.74rem; color: var(--text-muted); display: block; margin-top: 3px;">Ketik atau pilih divisi</small>
                    </div>

                    <!-- Pilihan Kelas Sesuai Mata Pelajaran -->
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" for="class_name" style="font-weight: 700; color: #1e293b;">
                            Pilihan Kelas <span style="color: #ef4444;">*</span>
                        </label>
                        <select name="class_name" id="class_name" class="form-control" required style="padding: 10px 14px; font-weight: 600;">
                            <option value="" disabled selected>-- Pilih Kelas --</option>
                        </select>
                        <small id="classHelperText" style="font-size: 0.74rem; color: var(--text-muted); display: block; margin-top: 3px;">Pilihan kelas sesuai mapel</small>
                    </div>
                </div>

                <!-- Pilihan Guru Pengajar (Khusus Bahasa Inggris jika ada beberapa instruktur) -->
                @if(isset($englishTeachers) && $englishTeachers->count() > 0)
                <div class="form-group" id="englishTeacherGroup" style="margin-bottom: 1.15rem; display: {{ old('subject_id', 1) == 1 ? 'block' : 'none' }};">
                    <label class="form-label" for="teacher_id" style="font-weight: 700; color: #1e293b;">
                        Pilih Guru Pengajar (Miss / Mr) <span style="color: #ef4444;">*</span>
                    </label>
                    <select name="teacher_id" id="teacher_id" class="form-control" style="padding: 10px 14px; font-weight: 600;">
                        @foreach($englishTeachers as $t)
                            <option value="{{ $t->id }}" {{ old('teacher_id') == $t->id ? 'selected' : '' }}>
                                {{ $t->name }}
                            </option>
                        @endforeach
                    </select>
                    <small style="font-size: 0.74rem; color: var(--text-muted); display: block; margin-top: 3px;">Materi, nilai, dan absensi Anda akan dibimbing oleh guru ini</small>
                </div>
                @endif

                <!-- Email & No. HP (2 Kolom) -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 1.15rem;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" for="email" style="font-weight: 700; color: #1e293b;">
                            Alamat Email <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required placeholder="email.anda@musashi.id" style="padding: 10px 14px;">
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" for="phone" style="font-weight: 700; color: #1e293b;">
                            No. WhatsApp / HP
                        </label>
                        <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx" style="padding: 10px 14px;">
                    </div>
                </div>

                <!-- Password & Konfirmasi Password -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 1.5rem;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" for="password" style="font-weight: 700; color: #1e293b;">
                            Kata Sandi <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="password" name="password" id="password" class="form-control" required placeholder="Min. 6 karakter" style="padding: 10px 14px;">
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" for="password_confirmation" style="font-weight: 700; color: #1e293b;">
                            Konfirmasi Sandi <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required placeholder="Ulangi sandi" style="padding: 10px 14px;">
                    </div>
                </div>

                <div id="subjectInfoBox" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: var(--radius-md); padding: 12px 16px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 12px;">
                    <span id="subjectInfoIcon" style="font-size: 1.3rem;">💡</span>
                    <span id="subjectInfoText" style="font-size: 0.84rem; color: #475569; line-height: 1.45;">
                        Pilih mata pelajaran di atas untuk melihat daftar kelas yang tersedia.
                    </span>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 13px; font-size: 1rem; font-weight: 800; border-radius: 10px; box-shadow: 0 4px 12px rgba(37,99,235,0.3);">
                    Daftar & Masuk Kelas Belajar &rarr;
                </button>
            </form>

            <div style="margin-top: 1.75rem; padding-top: 1.25rem; border-top: 1px solid var(--border-color); text-align: center;">
                <p style="font-size: 0.9rem; color: var(--text-muted); margin: 0;">
                    Sudah memiliki akun? 
                    <a href="{{ route('login') }}" style="font-weight: 700; color: var(--color-primary);">
                        Masuk (Login) di sini
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const classesData = @json($classes);
const oldClassName = @json(old('class_name'));
const subjectsMap = {
    1: { name: 'Bahasa Inggris', icon: '📚', desc: 'Kelas Bahasa Inggris berfokus pada 25 pertemuan mingguan dengan materi terstruktur.', helper: 'Pilih kelas: Class B1, Class I1, Class A1, dll.' },
    2: { name: 'Bahasa Jepang', icon: '🌸', desc: 'Kelas Bahasa Jepang menggunakan format 12 pertemuan belajar (Grup 1 sampai Grup 12).', helper: 'Pilih grup: Grup 1 sampai Grup 12' },
    3: { name: 'Matematika', icon: '🔢', desc: 'Kelas Matematika berfokus pada kalkulasi manufaktur dan logika hitung industri.', helper: 'Pilih kelas Matematika' }
};

function onSubjectChange(subjectId) {
    // 1. Update radio card styles
    document.querySelectorAll('[id^="label_subj_"]').forEach(el => {
        el.style.borderColor = '#e2e8f0';
        el.style.background = '#ffffff';
    });
    const activeLabel = document.getElementById('label_subj_' + subjectId);
    if (activeLabel) {
        activeLabel.style.borderColor = '#2563eb';
        activeLabel.style.background = '#eff6ff';
    }

    // 2. Update info box and icons
    const info = subjectsMap[subjectId] || { name: 'Program Belajar', icon: '🎓', desc: 'Silakan pilih kelas belajar.', helper: 'Pilih kelas' };
    const iconEl = document.getElementById('subjectHeaderIcon');
    if (iconEl) iconEl.innerText = info.icon;
    const infoIcon = document.getElementById('subjectInfoIcon');
    if (infoIcon) infoIcon.innerText = info.icon;
    const infoText = document.getElementById('subjectInfoText');
    if (infoText) infoText.innerHTML = `<strong>${info.name}:</strong> ${info.desc}`;
    const helperText = document.getElementById('classHelperText');
    if (helperText) helperText.innerText = info.helper;

    // Toggle teacher selector for English
    const teacherGroup = document.getElementById('englishTeacherGroup');
    if (teacherGroup) {
        teacherGroup.style.display = (subjectId == 1) ? 'block' : 'none';
    }

    // 3. Populate class dropdown
    const classSelect = document.getElementById('class_name');
    classSelect.innerHTML = '<option value="" disabled selected>-- ' + (subjectId == 2 ? 'Pilih Grup ' : 'Pilih Kelas ') + info.name + ' --</option>';

    const filtered = classesData.filter(c => c.subject_id == subjectId);
    if (filtered.length === 0) {
        const opt = document.createElement('option');
        opt.value = "";
        opt.disabled = true;
        opt.innerText = "Belum ada grup/kelas terdaftar";
        classSelect.appendChild(opt);
        return;
    }

    filtered.forEach(cls => {
        const opt = document.createElement('option');
        opt.value = cls.name;
        opt.innerText = cls.name;
        if (oldClassName && oldClassName === cls.name) {
            opt.selected = true;
        }
        classSelect.appendChild(opt);
    });
}

// Initial populate on load
document.addEventListener('DOMContentLoaded', function() {
    const selectedSubjInput = document.querySelector('input[name="subject_id"]:checked');
    const initialSubjId = selectedSubjInput ? parseInt(selectedSubjInput.value) : 1;
    onSubjectChange(initialSubjId);
});
</script>
@endpush
@endsection
