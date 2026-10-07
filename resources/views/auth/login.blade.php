@extends('layouts.app')

@section('title', 'Masuk ke Akun - Learning Musashi')

@section('content')
<div style="max-width: 520px; margin: 2rem auto; padding: 0 1rem;">
    <div class="card" style="box-shadow: 0 20px 35px -10px rgba(15, 23, 42, 0.12), 0 1px 3px rgba(0, 0, 0, 0.05); border-radius: 1.25rem; border: 1px solid #e2e8f0; overflow: hidden; background: #ffffff;">
        
        <!-- Header Branding -->
        <div style="padding: 2.25rem 2.25rem 1.5rem; text-align: center; background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%); border-bottom: 1px solid #f1f5f9;">
            <div style="display: flex; align-items: center; justify-content: center; gap: 14px; margin-bottom: 1rem;">
                <img src="{{ asset('images/bola.png') }}" alt="Logo Bola" style="height: 50px; width: auto; object-fit: contain;">
                <img src="{{ asset('images/Gambar1.png') }}" alt="MUSASHI" style="height: 38px; width: auto; object-fit: contain;">
            </div>
            <h1 id="portalHeaderTitle" style="font-size: 1.55rem; font-weight: 800; color: #0f172a; margin-bottom: 0.35rem; font-family: var(--font-heading);">
                Learning Musashi
            </h1>
            <p id="portalHeaderSubtitle" style="font-size: 0.9rem; color: #64748b; margin: 0;">
                Pilih nama peserta dan mata pelajaran untuk masuk ke pembelajaran
            </p>
        </div>

        <div class="card-body" style="padding: 2rem 2.25rem;">
            
            <!-- Alert Messages -->
            @if(session('info'))
                <div class="alert alert-info" style="margin-bottom: 1.25rem; border-radius: 10px; font-size: 0.9rem;">
                    <div>{{ session('info') }}</div>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success" style="margin-bottom: 1.25rem; border-radius: 10px; font-size: 0.9rem;">
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger" style="margin-bottom: 1.25rem; border-radius: 10px; font-size: 0.9rem;">
                    <div>{{ $errors->first() }}</div>
                </div>
            @endif

            <!-- Segmented Mode Tabs (Siswa vs Pengajar/Admin) -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; background: #f1f5f9; padding: 5px; border-radius: 12px; margin-bottom: 1.5rem;" id="loginModeNav">
                <button type="button" 
                        id="tabBtnStudent" 
                        onclick="switchLoginMode('student')" 
                        style="padding: 10px 12px; border-radius: 9px; border: none; font-weight: 700; font-size: 0.9rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; background: #ffffff; color: #1e293b; box-shadow: 0 2px 5px rgba(0,0,0,0.08); transition: all 0.2s;">
                    <span>🎓 Siswa / Peserta</span>
                </button>
                <button type="button" 
                        id="tabBtnStaff" 
                        onclick="switchLoginMode('staff')" 
                        style="padding: 10px 12px; border-radius: 9px; border: none; font-weight: 700; font-size: 0.9rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; background: transparent; color: #64748b; transition: all 0.2s;">
                    <span>👨‍🏫 Guru / Pengajar</span>
                </button>
            </div>

            <!-- ========================================== -->
            <!-- 1. FORM SISWA (SEARCHABLE DROPDOWN + MAPEL)-->
            <!-- ========================================== -->
            <div id="studentLoginFormSection">
                <form action="{{ route('login.post') }}" method="POST" id="studentLoginForm">
                    @csrf
                    <input type="hidden" name="login_type" value="student">
                    <input type="hidden" name="student_identifier" id="student_identifier" value="{{ old('student_identifier') }}">

                    <!-- ============================================== -->
                    <!-- LANGKAH 1: PILIH MATA PELAJARAN (FILTER UTAMA) -->
                    <!-- ============================================== -->
                    <div class="form-group" style="margin-bottom: 1.35rem;">
                        <label class="form-label" style="font-weight: 700; color: #1e293b; display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span>1. Pilih Mata Pelajaran</span>
                            <span id="subjectCounterText" style="font-size: 0.78rem; font-weight: 700; color: #2563eb; background: #eff6ff; padding: 2px 8px; border-radius: 6px;">
                                Memuat peserta...
                            </span>
                        </label>
                        <input type="hidden" name="subject_id" id="subject_id" value="{{ old('subject_id', 1) }}">
                        
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;" id="subjectSelectorGrid">
                            <!-- Bahasa Inggris -->
                            <button type="button" 
                                    class="subject-pill-btn {{ old('subject_id', 1) == 1 ? 'active' : '' }}" 
                                    onclick="pickSubject(1)"
                                    id="subBtn1"
                                    style="padding: 12px 8px; border-radius: 10px; border: 2px solid #e2e8f0; background: #ffffff; cursor: pointer; text-align: center; transition: all 0.2s;">
                                <div style="font-size: 0.88rem; font-weight: 800; color: #1e293b; line-height: 1.2;">Bahasa Inggris</div>
                            </button>

                            <!-- Bahasa Jepang -->
                            <button type="button" 
                                    class="subject-pill-btn {{ old('subject_id') == 2 ? 'active' : '' }}" 
                                    onclick="pickSubject(2)"
                                    id="subBtn2"
                                    style="padding: 12px 8px; border-radius: 10px; border: 2px solid #e2e8f0; background: #ffffff; cursor: pointer; text-align: center; transition: all 0.2s;">
                                <div style="font-size: 0.88rem; font-weight: 800; color: #1e293b; line-height: 1.2;">Bahasa Jepang</div>
                            </button>

                            <!-- Matematika -->
                            <button type="button" 
                                    class="subject-pill-btn {{ old('subject_id') == 3 ? 'active' : '' }}" 
                                    onclick="pickSubject(3)"
                                    id="subBtn3"
                                    style="padding: 12px 8px; border-radius: 10px; border: 2px solid #e2e8f0; background: #ffffff; cursor: pointer; text-align: center; transition: all 0.2s;">
                                <div style="font-size: 0.88rem; font-weight: 800; color: #1e293b; line-height: 1.2;">Matematika</div>
                            </button>
                        </div>
                        <div id="subjectHelpText" style="font-size: 0.76rem; color: #64748b; margin-top: 6px;">
                            Daftar nama peserta di bawah akan otomatis disaring sesuai mata pelajaran yang Anda pilih di atas.
                        </div>
                    </div>

                    <!-- ============================================== -->
                    <!-- LANGKAH 2: PILIH NAMA PESERTA (SEARCHABLE SELECT)-->
                    <!-- ============================================== -->
                    <div class="form-group" style="margin-bottom: 1.35rem; position: relative;">
                        <label class="form-label" style="font-weight: 700; color: #1e293b; display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <span id="studentSelectLabel">2. Pilih Nama Peserta</span>
                            <span style="font-size: 0.78rem; font-weight: 500; color: #64748b;">Ketik nama / NRP</span>
                        </label>

                        <!-- Search Input Box -->
                        <div style="position: relative;">
                            <input type="text" 
                                   id="studentSearchInput" 
                                   class="form-control" 
                                   autocomplete="off"
                                   placeholder="Ketik untuk mencari Nama atau NRP Anda..."
                                   style="padding: 12px 14px 12px 38px; font-size: 0.95rem; border-radius: 10px; border: 1.5px solid #cbd5e1; font-weight: 500;"
                                   onfocus="openStudentDropdown()"
                                   oninput="filterStudents(this.value)">
                            <div style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                            </div>
                            <button type="button" 
                                    id="clearStudentBtn" 
                                    onclick="clearSelectedStudent()" 
                                    style="display: none; position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #94a3b8; cursor: pointer; padding: 4px;"
                                    title="Bersihkan pilihan">
                                ✕
                            </button>
                        </div>

                        <!-- Dropdown List of Students Filtered by Selected Subject -->
                        <div id="studentDropdownList" 
                             style="display: none; position: absolute; top: calc(100% + 4px); left: 0; right: 0; max-height: 260px; overflow-y: auto; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15); z-index: 1000; padding: 4px;">
                            @if(isset($students) && $students->isNotEmpty())
                                @foreach($students as $st)
                                    @php
                                        $isEnglish = ((int)$st->subject_id === 1);
                                        $rawNrp = $st->nrp ? trim((string)$st->nrp) : '';
                                        if ($isEnglish && $rawNrp !== '') {
                                            $nrpLen = strlen($rawNrp);
                                            $displayNrp = ($nrpLen > 1) ? (str_repeat('*', $nrpLen - 1) . substr($rawNrp, -1)) : $rawNrp;
                                        } else {
                                            $displayNrp = $rawNrp ?: '-';
                                        }
                                    @endphp
                                    <div class="student-item" 
                                         onclick="selectStudent({{ $st->id }}, '{{ addslashes($st->name) }}', '{{ $st->nrp }}', '{{ $st->email }}', '{{ addslashes($st->class_name) }}', '{{ addslashes($st->division) }}', {{ $st->subject_id }})"
                                         data-id="{{ $st->id }}"
                                         data-subject-id="{{ $st->subject_id }}"
                                         data-name="{{ strtoupper($st->name) }}"
                                         data-nrp="{{ $st->nrp }}"
                                         data-masked-nrp="{{ $displayNrp }}"
                                         data-class="{{ strtoupper($st->class_name) }}"
                                         data-division="{{ strtoupper($st->division) }}"
                                         style="padding: 10px 12px; border-radius: 8px; cursor: pointer; transition: background 0.15s; border-bottom: 1px solid #f1f5f9;">
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 3px;">
                                            <span style="font-weight: 700; color: #0f172a; font-size: 0.92rem;">
                                                {{ $st->name }}
                                            </span>
                                            <span style="font-size: 0.76rem; font-weight: 800; background: #eff6ff; color: #1e40af; padding: 2px 7px; border-radius: 6px;">
                                                NRP: {{ $displayNrp }}
                                            </span>
                                        </div>
                                        <div style="font-size: 0.78rem; color: #64748b; display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                                            <span style="background: {{ $st->subject_id == 2 ? '#fdf2f8' : ($st->subject_id == 3 ? '#ecfdf5' : '#eff6ff') }}; color: {{ $st->subject_id == 2 ? '#be185d' : ($st->subject_id == 3 ? '#047857' : '#1e40af') }}; font-weight: 700; padding: 1px 7px; border-radius: 5px; border: 1px solid {{ $st->subject_id == 2 ? '#fbcfe8' : ($st->subject_id == 3 ? '#a7f3d0' : '#bfdbfe') }};">
                                                {{ $st->subject_id == 2 ? 'Grup:' : 'Kelas:' }} <strong>{{ $st->class_name ?: 'Reguler' }}</strong>
                                            </span>
                                            <span>•</span>
                                            <span>{{ $st->division ?: 'Musashi Trainee' }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div style="padding: 16px; text-align: center; color: #64748b; font-size: 0.88rem;">
                                    Belum ada data siswa terdaftar di database.
                                </div>
                            @endif
                            <div id="noStudentFoundMsg" style="display: none; padding: 14px; text-align: center; color: #94a3b8; font-size: 0.88rem;">
                                Tidak ditemukan nama peserta untuk mata pelajaran ini.
                            </div>
                        </div>

                        <!-- Confirmation Box: Muncul setelah siswa dipilih agar yakin NRP, Kelas & Password benar -->
                        <div id="selectedStudentConfirmation" 
                             style="display: none; margin-top: 10px; background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 10px; padding: 12px 14px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                                <div style="display: flex; align-items: center; gap: 6px; font-weight: 800; font-size: 0.85rem; color: #166534;">
                                    <span style="background: #22c55e; color: #fff; border-radius: 50%; width: 18px; height: 18px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem;">✓</span>
                                    <span>Konfirmasi Data Peserta Terpilih</span>
                                </div>
                                <span id="confSubjectBadge" style="font-size: 0.74rem; font-weight: 800; color: #15803d; background: #dcfce7; padding: 2px 8px; border-radius: 6px;">
                                    -
                                </span>
                            </div>
                            <div style="font-size: 0.86rem; color: #1e293b; line-height: 1.5;">
                                <div><strong>Nama:</strong> <span id="confName">-</span></div>
                                <div><strong>NRP:</strong> <span id="confNrp" style="color: #0284c7; font-weight: 800;">-</span></div>
                                <div style="font-size: 0.82rem; color: #334155; margin-top: 3px;">
                                    <strong>Mata Pelajaran:</strong> <span id="confSubject" style="font-weight: 700; color: #166534;">-</span> |
                                    <strong><span id="confClassLabel">Kelas:</span></strong> <span id="confClass" style="font-weight: 800; color: #047857;">-</span> |
                                    <strong>Bagian:</strong> <span id="confDiv">-</span>
                                </div>
                                <!-- Non-English: Tampilkan info kata sandi otomatis -->
                                <div id="confPasswordAutoBox" style="margin-top: 8px; padding: 7px 10px; background: #dcfce7; border-radius: 6px; font-size: 0.82rem; color: #14532d; display: flex; align-items: center; gap: 6px;">
                                    <span>🔑</span>
                                    <span>Kata sandi Anda: <strong id="confPassword" style="font-family: monospace; font-size: 0.9rem;">-</strong> (otomatis terisi)</span>
                                </div>
                                <!-- English: Tampilkan info kata sandi rahasia / diinput manual -->
                                <div id="confPasswordManualBox" style="display: none; margin-top: 8px; padding: 7px 10px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; font-size: 0.82rem; color: #1e40af; display: flex; align-items: center; gap: 6px;">
                                    <span>🔒</span>
                                    <span>Kata sandi tidak terisi otomatis. Silakan masukkan kata sandi yang telah diberikan oleh pengajar pada kolom di bawah.</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================== -->
                    <!-- LANGKAH 3: KATA SANDI (PASSWORD DEFAULT NRP@MUSASHI / MANUAL KHUSUS INGGRIS) -->
                    <!-- ============================================== -->
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <label class="form-label" for="password" style="font-weight: 700; color: #1e293b; margin: 0;">
                                3. Kata Sandi (Password)
                            </label>
                            <span id="passwordHintBadge" style="font-size: 0.76rem; color: #d97706; font-weight: 700;">
                                🔒 Input manual
                            </span>
                        </div>
                        <div style="position: relative;">
                            <input type="password" 
                                   name="password" 
                                   id="password" 
                                   class="form-control" 
                                   required 
                                   placeholder="Masukkan kata sandi yang telah diberikan..."
                                   style="padding: 12px 42px 12px 14px; font-size: 0.95rem; border-radius: 10px; border: 1.5px solid #cbd5e1;">
                            <button type="button" 
                                    onclick="togglePasswordVisibility('password', this)"
                                    style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #64748b; cursor: pointer; padding: 4px;"
                                    title="Tampilkan/Sembunyikan sandi">
                                👁️
                            </button>
                        </div>
                        <div id="passwordHelpText" style="font-size: 0.76rem; color: #64748b; margin-top: 5px;">
                            🔒 Khusus peserta Bahasa Inggris, kata sandi diberikan langsung oleh pengajar/panitia (tidak terisi otomatis demi keamanan).
                        </div>
                    </div>

                    <!-- Tombol Masuk Siswa -->
                    <button type="submit" 
                            class="btn btn-primary" 
                            style="width: 100%; padding: 13px; font-size: 1.02rem; font-weight: 700; border-radius: 10px; box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35); display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <span>Masuk ke Pembelajaran</span>
                        <span>&rarr;</span>
                    </button>
                </form>

                <!-- Tombol Beralih ke Login Pengajar / Guru -->
                <div style="margin-top: 1.5rem; text-align: center; border-top: 1px solid #f1f5f9; padding-top: 1.25rem;">
                    <button type="button" 
                            onclick="switchLoginMode('staff')" 
                            style="background: none; border: none; color: #2563eb; font-size: 0.88rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 8px; transition: all 0.2s;">
                        <span>👨‍🏫 Masuk sebagai Guru / Pengajar &rarr;</span>
                    </button>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- 2. FORM PENGAJAR & ADMINISTRATOR (STAFF)   -->
            <!-- ========================================== -->
            <div id="staffLoginFormSection" style="display: none;">
                <div style="margin-bottom: 1.25rem; background: #eff6ff; border-radius: 10px; padding: 12px 14px; border: 1px solid #bfdbfe; display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <div style="font-size: 0.88rem; font-weight: 800; color: #1e40af;">
                            Portal Akses Guru / Pengajar
                        </div>
                        <div style="font-size: 0.78rem; color: #3b82f6;">
                            Guru Bahasa Inggris, Bahasa Jepang, Matematika
                        </div>
                    </div>
                    <button type="button" 
                            onclick="switchLoginMode('student')" 
                            style="background: #ffffff; border: 1px solid #bfdbfe; color: #2563eb; font-size: 0.8rem; font-weight: 700; cursor: pointer; padding: 4px 10px; border-radius: 6px;">
                        &larr; Portal Siswa
                    </button>
                </div>

                <form action="{{ route('login.post') }}" method="POST">
                    @csrf
                    <input type="hidden" name="login_type" value="staff">

                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label class="form-label" for="staff_email" style="font-weight: 700; color: #1e293b;">
                            Email, Nomor Handphone, atau Nama Guru / Pengajar
                        </label>
                        <input type="text" 
                               name="email" 
                               id="staff_email" 
                               class="form-control" 
                               value="{{ old('email') }}" 
                               required 
                               placeholder="Contoh: adela11@musashi.id atau 081234567890"
                               style="padding: 12px 14px; font-size: 0.95rem; border-radius: 10px; border: 1.5px solid #cbd5e1;">
                        <small style="color: #64748b; font-size: 0.76rem; margin-top: 4px; display: block;">
                            💡 Anda dapat masuk menggunakan alamat email, nomor telepon WhatsApp, atau nama guru yang terdaftar di sistem.
                        </small>
                    </div>

                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label class="form-label" for="staff_password" style="font-weight: 700; color: #1e293b;">
                            Kata Sandi (Password)
                        </label>
                        <div style="position: relative;">
                            <input type="password" 
                                   name="password" 
                                   id="staff_password" 
                                   class="form-control" 
                                   required 
                                   placeholder="Masukkan kata sandi akun Anda"
                                   style="padding: 12px 42px 12px 14px; font-size: 0.95rem; border-radius: 10px; border: 1.5px solid #cbd5e1;">
                            <button type="button" 
                                    onclick="togglePasswordVisibility('staff_password', this)"
                                    style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #64748b; cursor: pointer; padding: 4px;"
                                    title="Tampilkan/Sembunyikan sandi">
                                👁️
                            </button>
                        </div>
                    </div>

                    <button type="submit" 
                            class="btn btn-primary" 
                            style="width: 100%; padding: 13px; font-size: 1rem; font-weight: 700; border-radius: 10px; display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <span>Masuk sebagai Guru / Pengajar</span>
                        <span>&rarr;</span>
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

@push('styles')
<style>
    .student-item:hover {
        background-color: #f8fafc !important;
    }
    .subject-pill-btn.active {
        border-color: #2563eb !important;
        background-color: #eff6ff !important;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
    }
    .subject-pill-btn.active div:first-child {
        transform: scale(1.1);
        transition: transform 0.2s;
    }
</style>
@endpush

@push('scripts')
<script>
    const subjectMeta = {
        1: { name: 'Bahasa Inggris', icon: '' },
        2: { name: 'Bahasa Jepang', icon: '' },
        3: { name: 'Matematika', icon: '' }
    };

    let selectedStudentSubjectId = null;

    // 1. Dropdown and Search Logic
    function openStudentDropdown() {
        const curSubjId = parseInt(document.getElementById('subject_id').value) || 1;
        filterStudents(document.getElementById('studentSearchInput').value);
        document.getElementById('studentDropdownList').style.display = 'block';
    }

    function closeStudentDropdown() {
        setTimeout(() => {
            document.getElementById('studentDropdownList').style.display = 'none';
        }, 200);
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        const input = document.getElementById('studentSearchInput');
        const list = document.getElementById('studentDropdownList');
        if (input && list && !input.contains(e.target) && !list.contains(e.target)) {
            list.style.display = 'none';
        }
    });

    function maskNrp(nrp) {
        if (!nrp) return '-';
        const s = String(nrp).trim();
        if (s.length <= 1) return s || '-';
        return '*'.repeat(s.length - 1) + s.slice(-1);
    }

    function updatePasswordSectionUI(subjId) {
        const passwordInput = document.getElementById('password');
        const passwordHintBadge = document.getElementById('passwordHintBadge');
        const passwordHelpText = document.getElementById('passwordHelpText');

        if (subjId === 1) {
            if (passwordHintBadge) {
                passwordHintBadge.innerText = '🔒 Input manual';
                passwordHintBadge.style.color = '#d97706';
            }
            if (passwordInput) {
                passwordInput.placeholder = 'Masukkan kata sandi yang telah diberikan...';
            }
            if (passwordHelpText) {
                passwordHelpText.innerHTML = '🔒 Khusus peserta Bahasa Inggris, kata sandi diberikan langsung oleh pengajar/panitia (tidak terisi otomatis demi keamanan).';
            }
        } else if (subjId === 2) {
            if (passwordHintBadge) {
                passwordHintBadge.innerText = 'Pola: [NRP]@jpnmusashi';
                passwordHintBadge.style.color = '#0284c7';
            }
            if (passwordInput) {
                passwordInput.placeholder = 'Contoh: 112@jpnmusashi';
            }
            if (passwordHelpText) {
                passwordHelpText.innerHTML = '🔒 Kata sandi peserta Bahasa Jepang: <strong>[NRP]@jpnmusashi</strong> (contoh: NRP <code>112</code> &rarr; sandi <code>112@jpnmusashi</code>). Otomatis terisi saat memilih nama.';
            }
        } else if (subjId === 3) {
            if (passwordHintBadge) {
                passwordHintBadge.innerText = 'Pola: [NRP]@mtkmusashi';
                passwordHintBadge.style.color = '#0284c7';
            }
            if (passwordInput) {
                passwordInput.placeholder = 'Contoh: 112@mtkmusashi';
            }
            if (passwordHelpText) {
                passwordHelpText.innerHTML = '🔒 Kata sandi peserta Matematika: <strong>[NRP]@mtkmusashi</strong> (contoh: NRP <code>112</code> &rarr; sandi <code>112@mtkmusashi</code>). Otomatis terisi saat memilih nama.';
            }
        } else {
            if (passwordHintBadge) {
                passwordHintBadge.innerText = 'Pola: [NRP]@musashi';
                passwordHintBadge.style.color = '#0284c7';
            }
            if (passwordInput) {
                passwordInput.placeholder = 'Contoh: 1367@musashi';
            }
            if (passwordHelpText) {
                passwordHelpText.innerHTML = '🔒 Kata sandi seluruh siswa diatur seragam: <strong>[NRP]@musashi</strong> (contoh: NRP <code>00001</code> &rarr; sandi <code>00001@musashi</code>, NRP <code>25698</code> &rarr; sandi <code>25698@musashi</code>).';
            }
        }
    }

    function filterStudents(query) {
        const currentSubjId = parseInt(document.getElementById('subject_id').value) || 1;
        const filter = (query || '').toUpperCase().trim();
        const items = document.querySelectorAll('.student-item');
        let visibleCount = 0;
        let totalInSubject = 0;

        items.forEach(item => {
            const itemSubjId = parseInt(item.getAttribute('data-subject-id'));
            if (itemSubjId === currentSubjId) {
                totalInSubject++;
                const name = item.getAttribute('data-name') || '';
                const nrp = item.getAttribute('data-nrp') || '';
                const maskedNrp = item.getAttribute('data-masked-nrp') || '';
                const className = item.getAttribute('data-class') || '';
                const division = item.getAttribute('data-division') || '';

                if (!filter || name.includes(filter) || nrp.includes(filter) || maskedNrp.includes(filter) || className.includes(filter) || division.includes(filter)) {
                    item.style.display = 'block';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            } else {
                item.style.display = 'none';
            }
        });

        const noFoundMsg = document.getElementById('noStudentFoundMsg');
        if (noFoundMsg) {
            if (visibleCount === 0) {
                const subjInfo = subjectMeta[currentSubjId] ? subjectMeta[currentSubjId].name : 'mata pelajaran ini';
                if (totalInSubject === 0) {
                    noFoundMsg.innerHTML = 'Belum ada data siswa terdaftar untuk <strong>' + subjInfo + '</strong> di database.';
                } else {
                    noFoundMsg.innerHTML = 'Tidak ditemukan peserta <strong>' + subjInfo + '</strong> yang cocok dengan kata kunci pencarian.';
                }
                noFoundMsg.style.display = 'block';
            } else {
                noFoundMsg.style.display = 'none';
            }
        }

        const clearBtn = document.getElementById('clearStudentBtn');
        if (clearBtn) {
            clearBtn.style.display = (query && query.length > 0) ? 'block' : 'none';
        }
    }

    function selectStudent(id, name, nrp, email, className, division, subjectId) {
        selectedStudentSubjectId = parseInt(subjectId);
        const isEnglish = (selectedStudentSubjectId === 1);

        // Set name in input
        document.getElementById('studentSearchInput').value = name;
        
        // Identifier is set to the exact student record ID!
        document.getElementById('student_identifier').value = id;

        const cleanNrp = nrp ? nrp.replace(/[^a-zA-Z0-9]/g, '') : '';
        let studentPassword = 'password';
        if (cleanNrp) {
            if (selectedStudentSubjectId === 2) {
                studentPassword = `${cleanNrp}@jpnmusashi`;
            } else if (selectedStudentSubjectId === 3) {
                studentPassword = `${cleanNrp}@mtkmusashi`;
            } else {
                studentPassword = `${cleanNrp}@musashi`;
            }
        }
        const passwordInput = document.getElementById('password');
        const passwordHintBadge = document.getElementById('passwordHintBadge');
        const confPasswordAutoBox = document.getElementById('confPasswordAutoBox');
        const confPasswordManualBox = document.getElementById('confPasswordManualBox');

        if (isEnglish) {
            // KHUSUS BAHASA INGGRIS: Tidak otomatis mengisi password!
            if (passwordInput) {
                passwordInput.value = '';
                passwordInput.placeholder = 'Masukkan kata sandi yang telah diberikan...';
                setTimeout(() => passwordInput.focus(), 150);
            }
            if (passwordHintBadge) {
                passwordHintBadge.innerText = '🔒 Input manual';
                passwordHintBadge.style.color = '#d97706';
            }
            if (confPasswordAutoBox) confPasswordAutoBox.style.display = 'none';
            if (confPasswordManualBox) confPasswordManualBox.style.display = 'flex';
        } else {
            // Bahasa Jepang & Matematika: Otomatis mengisi password
            if (passwordInput) {
                passwordInput.value = studentPassword;
                passwordInput.placeholder = 'Contoh: ' + studentPassword;
            }
            if (passwordHintBadge) {
                passwordHintBadge.innerText = 'Sandi: ' + studentPassword;
                passwordHintBadge.style.color = '#0284c7';
            }
            const confPassword = document.getElementById('confPassword');
            if (confPassword) confPassword.innerText = studentPassword;
            if (confPasswordAutoBox) confPasswordAutoBox.style.display = 'flex';
            if (confPasswordManualBox) confPasswordManualBox.style.display = 'none';
        }

        // Show confirmation badge with masked NRP for English students
        document.getElementById('confName').innerText = name;
        document.getElementById('confNrp').innerText = isEnglish ? maskNrp(nrp) : (nrp || '-');
        document.getElementById('confClass').innerText = className || 'Reguler';
        document.getElementById('confClassLabel').innerText = (selectedStudentSubjectId === 2 ? 'Grup:' : 'Kelas:');
        document.getElementById('confDiv').innerText = division || '-';

        const subjInfo = subjectMeta[selectedStudentSubjectId];
        const subjName = subjInfo ? subjInfo.name : '-';
        document.getElementById('confSubject').innerText = subjName;
        document.getElementById('confSubjectBadge').innerText = subjName.toUpperCase();

        document.getElementById('selectedStudentConfirmation').style.display = 'block';

        const clearBtn = document.getElementById('clearStudentBtn');
        if (clearBtn) clearBtn.style.display = 'block';

        document.getElementById('studentDropdownList').style.display = 'none';
    }

    function clearSelectedStudent() {
        document.getElementById('studentSearchInput').value = '';
        document.getElementById('student_identifier').value = '';
        document.getElementById('selectedStudentConfirmation').style.display = 'none';
        
        const passwordInput = document.getElementById('password');
        if (passwordInput) {
            passwordInput.value = '';
        }

        const curSubjId = parseInt(document.getElementById('subject_id').value) || 1;
        updatePasswordSectionUI(curSubjId);

        selectedStudentSubjectId = null;

        const clearBtn = document.getElementById('clearStudentBtn');
        if (clearBtn) clearBtn.style.display = 'none';

        filterStudents('');
    }

    // 2. Subject Picker Logic (Switches Subject and filters student list)
    function pickSubject(id) {
        document.getElementById('subject_id').value = id;

        // Update active class on 3 subject buttons
        for (let i = 1; i <= 3; i++) {
            const btn = document.getElementById('subBtn' + i);
            if (btn) {
                if (i === id) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            }
        }

        // Count students belonging to this subject
        const items = document.querySelectorAll('.student-item');
        let countInSubj = 0;
        items.forEach(item => {
            if (parseInt(item.getAttribute('data-subject-id')) === id) {
                countInSubj++;
            }
        });

        // Update badge / counter
        const subjInfo = subjectMeta[id] || { name: 'Mata Pelajaran', icon: '' };
        const counterEl = document.getElementById('subjectCounterText');
        if (counterEl) {
            counterEl.innerText = countInSubj + ' Siswa ' + subjInfo.name;
        }

        const labelEl = document.getElementById('studentSelectLabel');
        if (labelEl) {
            labelEl.innerText = '2. Pilih Nama Peserta';
        }

        const searchInput = document.getElementById('studentSearchInput');
        if (searchInput) {
            searchInput.placeholder = 'Cari Nama / NRP Siswa ' + subjInfo.name + '...';
        }

        // Update password section UI according to subject
        updatePasswordSectionUI(id);

        // If previously selected student was from a different subject, reset choice
        if (selectedStudentSubjectId && selectedStudentSubjectId !== id) {
            clearSelectedStudent();
        }

        // Re-filter dropdown list to active subject
        filterStudents(searchInput ? searchInput.value : '');
    }

    // Initialize subject picker on page load
    document.addEventListener('DOMContentLoaded', function() {
        const initSubjId = parseInt(document.getElementById('subject_id').value) || 1;
        pickSubject(initSubjId);
    });

    // 3. Password visibility toggle
    function togglePasswordVisibility(fieldId, btn) {
        const input = document.getElementById(fieldId);
        if (input.type === 'password') {
            input.type = 'text';
            btn.innerText = '🙈';
        } else {
            input.type = 'password';
            btn.innerText = '👁️';
        }
    }

    // 4. Switch Login Mode (Student vs Staff)
    function switchLoginMode(mode) {
        const studentSec = document.getElementById('studentLoginFormSection');
        const staffSec = document.getElementById('staffLoginFormSection');
        const headerTitle = document.getElementById('portalHeaderTitle');
        const headerSubtitle = document.getElementById('portalHeaderSubtitle');
        const tabStudent = document.getElementById('tabBtnStudent');
        const tabStaff = document.getElementById('tabBtnStaff');

        if (mode === 'staff') {
            studentSec.style.display = 'none';
            staffSec.style.display = 'block';
            headerTitle.innerText = 'Portal Guru / Pengajar';
            headerSubtitle.innerText = 'Masuk menggunakan email, nomor handphone, atau nama pengajar/admin';

            if (tabStudent && tabStaff) {
                tabStudent.style.background = 'transparent';
                tabStudent.style.color = '#64748b';
                tabStudent.style.boxShadow = 'none';
                tabStaff.style.background = '#ffffff';
                tabStaff.style.color = '#1e293b';
                tabStaff.style.boxShadow = '0 2px 5px rgba(0,0,0,0.08)';
            }
        } else {
            staffSec.style.display = 'none';
            studentSec.style.display = 'block';
            headerTitle.innerText = 'Learning Musashi';
            headerSubtitle.innerText = 'Pilih nama peserta dan mata pelajaran untuk masuk ke pembelajaran';

            if (tabStudent && tabStaff) {
                tabStaff.style.background = 'transparent';
                tabStaff.style.color = '#64748b';
                tabStaff.style.boxShadow = 'none';
                tabStudent.style.background = '#ffffff';
                tabStudent.style.color = '#1e293b';
                tabStudent.style.boxShadow = '0 2px 5px rgba(0,0,0,0.08)';
            }
        }
    }

    // Auto submit fallback if typed manually without clicking dropdown
    const studentForm = document.getElementById('studentLoginForm');
    if (studentForm) {
        studentForm.addEventListener('submit', function(e) {
            const identEl = document.getElementById('student_identifier');
            const searchInput = document.getElementById('studentSearchInput');
            if ((!identEl.value || identEl.value.trim() === '') && searchInput && searchInput.value.trim() !== '') {
                identEl.value = searchInput.value.trim();
            }
        });
    }

    // Auto switch to staff tab if there was an error in staff login
    @if(session('active_tab') === 'staff')
        switchLoginMode('staff');
    @endif
</script>
@endpush
@endsection
