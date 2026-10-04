@extends('layouts.app')

@section('title', 'Kelola Soal Latihan & Evaluasi - Super Admin - Musashi')

@section('content')
<div style="margin-bottom: 2rem;">
    <!-- Subject Selection Pills -->
    <div style="display: flex; gap: 10px; margin-bottom: 1.5rem; flex-wrap: wrap;">
        @foreach($subjects as $subj)
            @php
                $isActiveSubj = $subj->id === $currentSubject->id;
            @endphp
            <a href="{{ route('superadmin.questions.index', ['subject_id' => $subj->id]) }}"
               class="btn {{ $isActiveSubj ? 'btn-primary' : 'btn-secondary' }}"
               style="border-radius: 9999px; font-weight: 800; padding: 8px 22px; font-size: 0.95rem; display: inline-flex; align-items: center; box-shadow: {{ $isActiveSubj ? '0 4px 6px -1px rgba(37, 99, 235, 0.25)' : 'none' }};">
                <span>{{ $subj->name }}</span>
            </a>
        @endforeach
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px; flex-wrap: wrap;">
                <span class="badge" style="background: #eef2ff; color: #4338ca; font-weight: 800; font-size: 0.8rem; border: 1px solid #c7d2fe;">
                    Learning Musashi Super Admin
                </span>
                <span class="badge" style="background: #eff6ff; color: #1e40af; font-weight: 800; font-size: 0.8rem; border: 1px solid #bfdbfe;">
                    {{ $currentSubject->name }}
                </span>
                @if($isJapaneseTestMode)
                    <span class="badge" style="background: #fdf2f8; color: #db2777; font-weight: 800; font-size: 0.8rem; border: 1px solid #fbcfe8;">
                        🇯🇵 {{ $selectedJpCategory === 'per_pertemuan' ? 'Latihan Per Pertemuan (1-12)' : 'Ujian Evaluasi (Per 4 Pertemuan)' }}
                    </span>
                @endif
            </div>
            <h1 style="font-size: 1.85rem; margin: 0; font-weight: 800; color: #0f172a;">
                📝 Kelola Soal: {{ $currentSubject->name }}
            </h1>
            <p style="color: var(--text-muted); font-size: 0.92rem; margin-top: 4px;">
                @if($currentSubject->id == 1)
                    Kelola butir soal dan aktivasi pertemuan untuk <strong>25 Pertemuan</strong> siswa Bahasa Inggris. Siswa hanya dapat mengerjakan latihan pada pertemuan yang telah <strong>diaktifkan</strong> oleh Super Admin.
                @elseif($currentSubject->id == 2)
                    Kelola butir soal dan aktivasi untuk <strong>Latihan Per Pertemuan (1-12)</strong> serta <strong>Ujian Evaluasi (Per 4 Pertemuan)</strong>. Siswa kelas Jepang hanya dapat mengakses modul yang telah <strong>diaktifkan</strong>.
                @else
                    Kelola soal latihan modul pembelajaran Matematika industri.
                @endif
            </p>
        </div>

        <div>
            @if($isJapaneseTestMode && $currentTest)
                <button type="button" class="btn btn-primary" onclick="openCreateQuestionModal(true, {{ $currentTest->id }})" style="font-weight: 700;">
                    ➕ Tambah Soal ke {{ $currentTest->category === 'per_pertemuan' ? 'Pertemuan ' . $currentTest->start_meeting : $currentTest->title }}
                </button>
            @elseif($currentLevel)
                <button type="button" class="btn btn-primary" onclick="openCreateQuestionModal(false, {{ $currentLevel->id }})" style="font-weight: 700;">
                    ➕ Tambah Soal {{ $currentLevel->name }} Baru
                </button>
            @endif
        </div>
    </div>
</div>

<!-- Japanese Category Tabs Switcher (Subject 2) -->
@if($currentSubject->id == 2)
    <div style="display: flex; gap: 10px; margin-bottom: 1.5rem; border-bottom: 2px solid #e2e8f0; padding-bottom: 4px;">
        <a href="{{ route('superadmin.questions.index', ['subject_id' => 2, 'category' => 'per_pertemuan']) }}"
           style="padding: 10px 18px; font-weight: 800; font-size: 0.95rem; text-decoration: none; border-bottom: 3px solid {{ $selectedJpCategory === 'per_pertemuan' ? '#db2777' : 'transparent' }}; color: {{ $selectedJpCategory === 'per_pertemuan' ? '#db2777' : '#64748b' }}; display: flex; align-items: center; gap: 8px;">
            <span>🗓️ Latihan Soal Per Pertemuan</span>
            <span class="badge" style="background: #fdf2f8; color: #be185d; font-size: 0.75rem;">12 Pertemuan (10 Soal)</span>
        </a>
        <a href="{{ route('superadmin.questions.index', ['subject_id' => 2, 'category' => 'per_4_pertemuan']) }}"
           style="padding: 10px 18px; font-weight: 800; font-size: 0.95rem; text-decoration: none; border-bottom: 3px solid {{ $selectedJpCategory === 'per_4_pertemuan' ? '#db2777' : 'transparent' }}; color: {{ $selectedJpCategory === 'per_4_pertemuan' ? '#db2777' : '#64748b' }}; display: flex; align-items: center; gap: 8px;">
            <span>🏆 Ujian Evaluasi Per 4 Pertemuan</span>
            <span class="badge" style="background: #eff6ff; color: #1e40af; font-size: 0.75rem;">3 Modul (15 Soal)</span>
        </a>
    </div>

    <!-- Bulk Activation Bar for Japanese -->
    <div class="card" style="margin-bottom: 1.5rem; border: 1px solid #fbcfe8; background: linear-gradient(135deg, #fff1f2 0%, #fdf2f8 100%); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
        <div class="card-body" style="padding: 1.15rem 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="font-size: 1.6rem; background: #ffffff; width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: var(--shadow-sm);">
                    ⚡
                </div>
                <div>
                    <strong style="color: #881337; font-size: 0.96rem; display: block;">Aktivasi Massal Soal Bahasa Jepang (Super Admin)</strong>
                    <span style="font-size: 0.82rem; color: #9f1239;">Aktifkan atau kunci seluruh modul soal sekaligus agar siswa kelas Jepang dapat/tidak dapat melihat soal.</span>
                </div>
            </div>

            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                @if($selectedJpCategory === 'per_pertemuan')
                    <form action="{{ route('superadmin.questions.japanese.toggle-all') }}" method="POST" style="margin: 0;">
                        @csrf
                        <input type="hidden" name="action" value="activate">
                        <input type="hidden" name="category" value="per_pertemuan">
                        <button type="submit" class="btn btn-sm" style="background: #059669; color: #ffffff; font-weight: 700; border: none; padding: 6px 14px;" onclick="return confirm('Aktifkan SEMUA 12 Latihan Per Pertemuan untuk siswa?')">
                            ▶ Aktifkan Semua Latihan (1-12)
                        </button>
                    </form>
                    <form action="{{ route('superadmin.questions.japanese.toggle-all') }}" method="POST" style="margin: 0;">
                        @csrf
                        <input type="hidden" name="action" value="deactivate">
                        <input type="hidden" name="category" value="per_pertemuan">
                        <button type="submit" class="btn btn-sm" style="background: #e2e8f0; color: #334155; font-weight: 700; border: 1px solid #cbd5e1; padding: 6px 14px;" onclick="return confirm('Kunci/Nonaktifkan SEMUA 12 Latihan Per Pertemuan?')">
                            ⏸ Kunci Semua Latihan
                        </button>
                    </form>
                @else
                    <form action="{{ route('superadmin.questions.japanese.toggle-all') }}" method="POST" style="margin: 0;">
                        @csrf
                        <input type="hidden" name="action" value="activate">
                        <input type="hidden" name="category" value="per_4_pertemuan">
                        <button type="submit" class="btn btn-sm" style="background: #db2777; color: #ffffff; font-weight: 700; border: none; padding: 6px 14px;" onclick="return confirm('Aktifkan SEMUA 3 Ujian Evaluasi Per 4 Pertemuan?')">
                            ▶ Aktifkan Semua Evaluasi (1-3)
                        </button>
                    </form>
                    <form action="{{ route('superadmin.questions.japanese.toggle-all') }}" method="POST" style="margin: 0;">
                        @csrf
                        <input type="hidden" name="action" value="deactivate">
                        <input type="hidden" name="category" value="per_4_pertemuan">
                        <button type="submit" class="btn btn-sm" style="background: #e2e8f0; color: #334155; font-weight: 700; border: 1px solid #cbd5e1; padding: 6px 14px;" onclick="return confirm('Kunci/Nonaktifkan SEMUA 3 Ujian Evaluasi?')">
                            ⏸ Kunci Semua Evaluasi
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Japanese Test Selector & Status Card -->
    <div class="card" style="margin-bottom: 1.75rem; box-shadow: var(--shadow-sm); border-radius: var(--radius-md); border-top: 4px solid {{ $currentTest && $currentTest->is_active ? '#10b981' : '#f59e0b' }};">
        <div class="card-body" style="padding: 1.15rem 1.5rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                    <label for="selectTest" style="font-weight: 800; color: #0f172a; font-size: 0.92rem; margin: 0;">
                        {{ $selectedJpCategory === 'per_pertemuan' ? 'Pilih Pertemuan Latihan:' : 'Pilih Modul Evaluasi:' }}
                    </label>
                    <select id="selectTest" class="form-control" style="width: auto; min-width: 280px; font-weight: 700;" onchange="window.location.href='{{ route('superadmin.questions.index', ['subject_id' => 2, 'category' => $selectedJpCategory]) }}&test_id=' + this.value">
                        @foreach($activeJpTests as $jt)
                            <option value="{{ $jt->id }}" {{ $currentTest && $currentTest->id === $jt->id ? 'selected' : '' }}>
                                @if($jt->category === 'per_pertemuan')
                                    🗓️ Pertemuan {{ $jt->start_meeting }} [{{ $jt->is_active ? '🟢 Aktif' : '🔒 Nonaktif' }}] ({{ $jt->questions_count }} Soal)
                                @else
                                    🏆 {{ $jt->title }} [{{ $jt->is_active ? '🟢 Aktif' : '🔒 Nonaktif' }}] ({{ $jt->questions_count }} Soal)
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                @if($currentTest)
                    <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                        <!-- Status Badge -->
                        <div>
                            @if($currentTest->is_active)
                                <span class="badge" style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; font-weight: 800; font-size: 0.85rem; padding: 6px 12px;">
                                    🟢 Aktif di Siswa
                                </span>
                            @else
                                <span class="badge" style="background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; font-weight: 800; font-size: 0.85rem; padding: 6px 12px;">
                                    ⚪ Terkunci (Nonaktif)
                                </span>
                            @endif
                        </div>

                        <!-- Individual Toggle Active Button -->
                        <form action="{{ route('superadmin.questions.japanese.toggle-active', $currentTest) }}" method="POST" style="margin: 0;">
                            @csrf
                            @if($currentTest->is_active)
                                <button type="submit" class="btn btn-sm" style="background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; font-weight: 700; padding: 6px 12px;" onclick="return confirm('Kunci/Nonaktifkan modul soal ini? Siswa tidak akan dapat melihatnya.')">
                                    ⏸ Nonaktifkan Pertemuan
                                </button>
                            @else
                                <button type="submit" class="btn btn-sm" style="background: #059669; color: #ffffff; border: 1px solid #059669; font-weight: 700; padding: 6px 12px;" onclick="return confirm('Aktifkan modul soal ini untuk siswa kelas Jepang?')">
                                    ▶ Aktifkan untuk Siswa
                                </button>
                            @endif
                        </form>

                        <!-- Button Edit Test Settings -->
                        <button type="button" class="btn btn-secondary btn-sm" onclick='openEditTestModal(@json($currentTest))' style="font-weight: 700; padding: 6px 12px;">
                            ⚙️ Atur Modul
                        </button>
                    </div>
                @endif
            </div>

            @if($currentTest)
                <div style="margin-top: 10px; padding-top: 10px; border-top: 1px dashed #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; font-size: 0.84rem; color: #475569;">
                    <div>
                        <strong>Judul:</strong> {{ $currentTest->title }}
                        @if($currentTest->description)
                            <span style="color: #64748b; margin-left: 8px;">— {{ $currentTest->description }}</span>
                        @endif
                    </div>
                    <div style="display: flex; gap: 14px;">
                        <span>Target Soal Acak: <strong style="color: #db2777;">{{ $currentTest->target_questions ?: 10 }} Soal</strong></span>
                        <span>Durasi: <strong style="color: #0f172a;">{{ $currentTest->duration_minutes }} Menit</strong></span>
                        <span>KKM: <strong style="color: #059669;">{{ $currentTest->pass_score }}%</strong></span>
                    </div>
                </div>
            @endif
        </div>
    </div>

@else
    <!-- Subject 1 (English) or Subject 3 (Math) Section -->

    <!-- Bulk Activation Bar for English Levels -->
    <div class="card" style="margin-bottom: 1.5rem; border: 1px solid #bfdbfe; background: linear-gradient(135deg, #eff6ff 0%, #f8fafc 100%); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
        <div class="card-body" style="padding: 1.15rem 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="font-size: 1.6rem; background: #ffffff; width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: var(--shadow-sm);">
                    ⚡
                </div>
                <div>
                    <strong style="color: #1e3a8a; font-size: 0.96rem; display: block;">Aksi Cepat Aktivasi Pertemuan {{ $currentSubject->name }}</strong>
                    <span style="font-size: 0.82rem; color: #3b82f6;">Atur akses latihan soal siswa per pertemuan. Siswa hanya dapat mengerjakan pertemuan yang berstatus AKTIF.</span>
                </div>
            </div>

            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <form action="{{ route('superadmin.questions.levels.toggle-all') }}" method="POST" style="margin: 0;">
                    @csrf
                    <input type="hidden" name="action" value="activate">
                    <input type="hidden" name="subject_id" value="{{ $currentSubject->id }}">
                    <button type="submit" class="btn btn-sm" style="background: #059669; color: #ffffff; font-weight: 700; border: none; padding: 6px 14px;" onclick="return confirm('Aktifkan SEMUA {{ $levels->count() }} Pertemuan {{ $currentSubject->name }} untuk siswa?')">
                        ▶ Aktifkan Semua Pertemuan (1-{{ $levels->count() }})
                    </button>
                </form>

                <form action="{{ route('superadmin.questions.levels.toggle-all') }}" method="POST" style="margin: 0;">
                    @csrf
                    <input type="hidden" name="action" value="deactivate">
                    <input type="hidden" name="subject_id" value="{{ $currentSubject->id }}">
                    <button type="submit" class="btn btn-sm" style="background: #e2e8f0; color: #334155; font-weight: 700; border: 1px solid #cbd5e1; padding: 6px 14px;" onclick="return confirm('Kunci/Nonaktifkan SEMUA pertemuan {{ $currentSubject->name }}?')">
                        ⏸ Kunci Semua Pertemuan
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Level / Meeting Selector Bar -->
    <div class="card" style="margin-bottom: 1.75rem; box-shadow: var(--shadow-sm); border-radius: var(--radius-md); border-top: 4px solid {{ $currentLevel && $currentLevel->is_active ? '#10b981' : '#f59e0b' }};">
        <div class="card-body" style="padding: 1.15rem 1.5rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                    <label for="selectLevel" style="font-weight: 800; color: #0f172a; font-size: 0.92rem; margin: 0;">
                        Pilih Pertemuan:
                    </label>
                    <select id="selectLevel" class="form-control" style="width: auto; min-width: 280px; font-weight: 700;" onchange="window.location.href='{{ route('superadmin.questions.index', ['subject_id' => $currentSubject->id]) }}&level_id=' + this.value">
                        @foreach($levels as $lvl)
                            <option value="{{ $lvl->id }}" {{ $currentLevel && $currentLevel->id === $lvl->id ? 'selected' : '' }}>
                                🗓️ {{ $lvl->name }} [{{ $lvl->is_active ? '🟢 Aktif' : '🔒 Nonaktif' }}] ({{ $lvl->exercises_count }} Soal)
                            </option>
                        @endforeach
                    </select>
                </div>

                @if($currentLevel)
                    <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                        <!-- Status Badge -->
                        <div>
                            @if($currentLevel->is_active)
                                <span class="badge" style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; font-weight: 800; font-size: 0.85rem; padding: 6px 12px;">
                                    🟢 Aktif di Siswa
                                </span>
                            @else
                                <span class="badge" style="background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; font-weight: 800; font-size: 0.85rem; padding: 6px 12px;">
                                    ⚪ Terkunci (Nonaktif)
                                </span>
                            @endif
                        </div>

                        <!-- Individual Level Toggle Active Button -->
                        <form action="{{ route('superadmin.questions.levels.toggle-active', $currentLevel) }}" method="POST" style="margin: 0;">
                            @csrf
                            @if($currentLevel->is_active)
                                <button type="submit" class="btn btn-sm" style="background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; font-weight: 700; padding: 6px 12px;" onclick="return confirm('Kunci/Nonaktifkan latihan {{ $currentLevel->name }}? Siswa tidak akan dapat mengakses soal ini.')">
                                    ⏸ Nonaktifkan Pertemuan Ini
                                </button>
                            @else
                                <button type="submit" class="btn btn-sm" style="background: #059669; color: #ffffff; border: 1px solid #059669; font-weight: 700; padding: 6px 12px;" onclick="return confirm('Aktifkan {{ $currentLevel->name }} untuk siswa?')">
                                    ▶ Aktifkan untuk Siswa
                                </button>
                            @endif
                        </form>

                        <div style="display: flex; gap: 12px; font-size: 0.85rem; color: #475569;">
                            <span>Jumlah Soal: <strong style="color: #0f172a;">{{ $exercises->count() }} Butir</strong></span>
                            <span>Syarat Lulus: <strong style="color: #059669;">{{ $currentLevel->required_points }} Pts</strong></span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif

<!-- Questions List Table Card -->
<div class="card" style="box-shadow: var(--shadow-sm); border-radius: var(--radius-lg); overflow: hidden;">
    <div class="card-header" style="background: #ffffff; border-bottom: 1px solid var(--border-color); padding: 1.15rem 1.5rem; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2 style="font-size: 1.15rem; margin: 0; font-weight: 800; color: #0f172a;">
                @if($isJapaneseTestMode && $currentTest)
                    Daftar Butir Soal: {{ $currentTest->title }}
                @elseif($currentLevel)
                    Daftar Butir Soal: {{ $currentLevel->name }} ({{ $currentSubject->name }})
                @else
                    Daftar Butir Soal
                @endif
            </h2>
            <div style="font-size: 0.82rem; color: var(--text-muted); margin-top: 2px;">
                Semua butir soal yang Anda simpan di sini akan langsung digunakan saat siswa mengerjakan latihan.
            </div>
        </div>
    </div>

    <div class="card-body" style="padding: 0;">
        @php
            $activeQuestionsList = $isJapaneseTestMode ? $testQuestions : $exercises;
        @endphp

        <div class="table-responsive">
            <table class="table" style="margin: 0;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                        <th style="width: 60px; text-align: center;">No</th>
                        <th>Pertanyaan & Opsi Pilihan Jawaban</th>
                        <th style="width: 110px; text-align: center;">Kunci Benar</th>
                        <th style="width: 90px; text-align: center;">Poin</th>
                        <th style="width: 150px; text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activeQuestionsList as $q)
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="text-align: center; vertical-align: top; padding-top: 1.1rem;">
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: #eff6ff; color: #1e40af; font-weight: 800; font-size: 0.85rem; display: inline-flex; align-items: center; justify-content: center; border: 1px solid #bfdbfe;">
                                    #{{ $q->question_number }}
                                </div>
                            </td>
                            <td style="vertical-align: top; padding: 1.1rem 1rem;">
                                <div style="font-size: 0.98rem; font-weight: 700; color: #0f172a; margin-bottom: 8px; line-height: 1.45;">
                                    {{ $q->question }}
                                </div>

                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 6px; font-size: 0.84rem; margin-bottom: 6px;">
                                    <div style="padding: 6px 10px; border-radius: 6px; background: {{ strtolower($q->correct_option) === 'a' ? '#ecfdf5' : '#f8fafc' }}; border: 1px solid {{ strtolower($q->correct_option) === 'a' ? '#a7f3d0' : '#e2e8f0' }}; color: {{ strtolower($q->correct_option) === 'a' ? '#065f46' : '#334155' }}; font-weight: {{ strtolower($q->correct_option) === 'a' ? '700' : '500' }};">
                                        <strong>A:</strong> {{ $q->option_a }}
                                        @if(strtolower($q->correct_option) === 'a') <span style="color: #059669; font-weight: 900;">✓</span> @endif
                                    </div>
                                    <div style="padding: 6px 10px; border-radius: 6px; background: {{ strtolower($q->correct_option) === 'b' ? '#ecfdf5' : '#f8fafc' }}; border: 1px solid {{ strtolower($q->correct_option) === 'b' ? '#a7f3d0' : '#e2e8f0' }}; color: {{ strtolower($q->correct_option) === 'b' ? '#065f46' : '#334155' }}; font-weight: {{ strtolower($q->correct_option) === 'b' ? '700' : '500' }};">
                                        <strong>B:</strong> {{ $q->option_b }}
                                        @if(strtolower($q->correct_option) === 'b') <span style="color: #059669; font-weight: 900;">✓</span> @endif
                                    </div>
                                    <div style="padding: 6px 10px; border-radius: 6px; background: {{ strtolower($q->correct_option) === 'c' ? '#ecfdf5' : '#f8fafc' }}; border: 1px solid {{ strtolower($q->correct_option) === 'c' ? '#a7f3d0' : '#e2e8f0' }}; color: {{ strtolower($q->correct_option) === 'c' ? '#065f46' : '#334155' }}; font-weight: {{ strtolower($q->correct_option) === 'c' ? '700' : '500' }};">
                                        <strong>C:</strong> {{ $q->option_c }}
                                        @if(strtolower($q->correct_option) === 'c') <span style="color: #059669; font-weight: 900;">✓</span> @endif
                                    </div>
                                    <div style="padding: 6px 10px; border-radius: 6px; background: {{ strtolower($q->correct_option) === 'd' ? '#ecfdf5' : '#f8fafc' }}; border: 1px solid {{ strtolower($q->correct_option) === 'd' ? '#a7f3d0' : '#e2e8f0' }}; color: {{ strtolower($q->correct_option) === 'd' ? '#065f46' : '#334155' }}; font-weight: {{ strtolower($q->correct_option) === 'd' ? '700' : '500' }};">
                                        <strong>D:</strong> {{ $q->option_d }}
                                        @if(strtolower($q->correct_option) === 'd') <span style="color: #059669; font-weight: 900;">✓</span> @endif
                                    </div>
                                </div>

                                @if($q->explanation)
                                    <div style="font-size: 0.78rem; color: #64748b; font-style: italic; background: #fffbeb; padding: 4px 8px; border-radius: 4px; display: inline-block;">
                                        💡 Pembahasan: {{ $q->explanation }}
                                    </div>
                                @endif
                            </td>
                            <td style="text-align: center; vertical-align: top; padding-top: 1.1rem;">
                                <span class="badge" style="background: #ecfdf5; color: #065f46; font-size: 0.88rem; font-weight: 800; border: 1px solid #a7f3d0; padding: 4px 10px;">
                                    {{ strtoupper($q->correct_option) }}
                                </span>
                            </td>
                            <td style="text-align: center; vertical-align: top; padding-top: 1.1rem;">
                                <span class="badge" style="background: #f1f5f9; color: #334155; font-weight: 700; font-size: 0.82rem;">
                                    +{{ $q->points }} Pts
                                </span>
                            </td>
                            <td style="text-align: right; vertical-align: top; padding-top: 1.1rem;">
                                <div style="display: inline-flex; gap: 6px;">
                                    <button type="button" class="btn btn-secondary btn-sm"
                                            onclick='openEditQuestionModal(@json($q), {{ $isJapaneseTestMode ? "true" : "false" }})'
                                            style="padding: 4px 10px; font-weight: 700; font-size: 0.8rem;">
                                        ✏️ Edit
                                    </button>

                                    <form action="{{ route('superadmin.questions.destroy', $q->id) }}" method="POST" style="margin: 0;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus butir soal #{{ $q->question_number }} ini?')">
                                        @csrf
                                        @method('DELETE')
                                        @if($isJapaneseTestMode)
                                            <input type="hidden" name="is_japanese_test" value="1">
                                        @endif
                                        <button type="submit" class="btn btn-danger btn-sm" style="padding: 4px 10px; font-weight: 700; font-size: 0.8rem;">
                                            🗑️ Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 3rem 1.5rem; color: var(--text-muted);">
                                <div style="font-size: 2.2rem; margin-bottom: 0.5rem;">📝</div>
                                <div style="font-weight: 700; font-size: 1.05rem; color: #0f172a;">Belum Ada Butir Soal</div>
                                <p style="font-size: 0.88rem; margin: 4px 0 1rem 0;">Belum ada butir soal yang ditambahkan pada modul pertemuan ini.</p>
                                @if($isJapaneseTestMode && $currentTest)
                                    <button type="button" class="btn btn-primary btn-sm" onclick="openCreateQuestionModal(true, {{ $currentTest->id }})">
                                        ➕ Tambah Soal Pertama
                                    </button>
                                @elseif($currentLevel)
                                    <button type="button" class="btn btn-primary btn-sm" onclick="openCreateQuestionModal(false, {{ $currentLevel->id }})">
                                        ➕ Tambah Soal Pertama
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ================= MODAL: TAMBAH SOAL ================= -->
<div id="createQuestionModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 9999; align-items: center; justify-content: center; padding: 1.5rem; backdrop-filter: blur(2px);">
    <div style="background: #ffffff; border-radius: var(--radius-lg); width: 100%; max-width: 620px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); overflow: hidden; max-height: 90vh; display: flex; flex-direction: column;">
        <div style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 1.15rem 1.5rem; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 1.15rem; margin: 0; font-weight: 800; color: #0f172a;" id="createModalHeaderTitle">
                ➕ Tambah Butir Soal Baru
            </h3>
            <button type="button" onclick="closeCreateQuestionModal()" style="background: none; border: none; font-size: 1.5rem; line-height: 1; color: #64748b; cursor: pointer;">&times;</button>
        </div>

        <form action="{{ route('superadmin.questions.store') }}" method="POST" style="margin: 0; overflow-y: auto; padding: 1.5rem; display: flex; flex-direction: column; gap: 1.1rem;">
            @csrf
            <input type="hidden" name="is_japanese_test" id="create_is_japanese_test" value="0">
            <input type="hidden" name="level_id" id="create_level_id" value="">
            <input type="hidden" name="test_id" id="create_test_id" value="">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div>
                    <label class="form-label" for="create_question_number" style="font-weight: 700;">Nomor Urut Soal *</label>
                    <input type="number" name="question_number" id="create_question_number" class="form-control" value="{{ $nextQuestionNumber }}" min="1" required>
                </div>
                <div>
                    <label class="form-label" for="create_points" style="font-weight: 700;">Poin Jawaban Benar *</label>
                    <input type="number" name="points" id="create_points" class="form-control" value="10" min="1" max="100" required>
                </div>
            </div>

            <div>
                <label class="form-label" for="create_question" style="font-weight: 700;">Pertanyaan Soal *</label>
                <textarea name="question" id="create_question" class="form-control" rows="3" required placeholder="Tuliskan teks pertanyaan..."></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <div>
                    <label class="form-label" for="create_option_a" style="font-weight: 700;">Pilihan A *</label>
                    <input type="text" name="option_a" id="create_option_a" class="form-control" required placeholder="Pilihan jawaban A">
                </div>
                <div>
                    <label class="form-label" for="create_option_b" style="font-weight: 700;">Pilihan B *</label>
                    <input type="text" name="option_b" id="create_option_b" class="form-control" required placeholder="Pilihan jawaban B">
                </div>
                <div>
                    <label class="form-label" for="create_option_c" style="font-weight: 700;">Pilihan C *</label>
                    <input type="text" name="option_c" id="create_option_c" class="form-control" required placeholder="Pilihan jawaban C">
                </div>
                <div>
                    <label class="form-label" for="create_option_d" style="font-weight: 700;">Pilihan D *</label>
                    <input type="text" name="option_d" id="create_option_d" class="form-control" required placeholder="Pilihan jawaban D">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div>
                    <label class="form-label" for="create_correct_option" style="font-weight: 700;">Kunci Jawaban Benar *</label>
                    <select name="correct_option" id="create_correct_option" class="form-control" required style="font-weight: 700;">
                        <option value="a">A</option>
                        <option value="b">B</option>
                        <option value="c">C</option>
                        <option value="d">D</option>
                    </select>
                </div>
                <div>
                    <label class="form-label" for="create_explanation" style="font-weight: 700;">Pembahasan (Opsional)</label>
                    <input type="text" name="explanation" id="create_explanation" class="form-control" placeholder="Penjelasan jawaban...">
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 0.5rem; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
                <button type="button" class="btn btn-secondary" onclick="closeCreateQuestionModal()">Batal</button>
                <button type="submit" class="btn btn-primary" style="font-weight: 700;">💾 Simpan Soal</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: EDIT SOAL ================= -->
<div id="editQuestionModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 9999; align-items: center; justify-content: center; padding: 1.5rem; backdrop-filter: blur(2px);">
    <div style="background: #ffffff; border-radius: var(--radius-lg); width: 100%; max-width: 620px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); overflow: hidden; max-height: 90vh; display: flex; flex-direction: column;">
        <div style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 1.15rem 1.5rem; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 1.15rem; margin: 0; font-weight: 800; color: #0f172a;" id="editModalHeaderTitle">
                ✏️ Edit Butir Soal
            </h3>
            <button type="button" onclick="closeEditQuestionModal()" style="background: none; border: none; font-size: 1.5rem; line-height: 1; color: #64748b; cursor: pointer;">&times;</button>
        </div>

        <form id="editQuestionForm" method="POST" style="margin: 0; overflow-y: auto; padding: 1.5rem; display: flex; flex-direction: column; gap: 1.1rem;">
            @csrf
            @method('PUT')
            <input type="hidden" name="is_japanese_test" id="edit_is_japanese_test" value="0">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div>
                    <label class="form-label" for="edit_question_number" style="font-weight: 700;">Nomor Urut Soal *</label>
                    <input type="number" name="question_number" id="edit_question_number" class="form-control" min="1" required>
                </div>
                <div>
                    <label class="form-label" for="edit_points" style="font-weight: 700;">Poin Jawaban Benar *</label>
                    <input type="number" name="points" id="edit_points" class="form-control" min="1" max="100" required>
                </div>
            </div>

            <div>
                <label class="form-label" for="edit_question" style="font-weight: 700;">Pertanyaan Soal *</label>
                <textarea name="question" id="edit_question" class="form-control" rows="3" required></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <div>
                    <label class="form-label" for="edit_option_a" style="font-weight: 700;">Pilihan A *</label>
                    <input type="text" name="option_a" id="edit_option_a" class="form-control" required>
                </div>
                <div>
                    <label class="form-label" for="edit_option_b" style="font-weight: 700;">Pilihan B *</label>
                    <input type="text" name="option_b" id="edit_option_b" class="form-control" required>
                </div>
                <div>
                    <label class="form-label" for="edit_option_c" style="font-weight: 700;">Pilihan C *</label>
                    <input type="text" name="option_c" id="edit_option_c" class="form-control" required>
                </div>
                <div>
                    <label class="form-label" for="edit_option_d" style="font-weight: 700;">Pilihan D *</label>
                    <input type="text" name="option_d" id="edit_option_d" class="form-control" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div>
                    <label class="form-label" for="edit_correct_option" style="font-weight: 700;">Kunci Jawaban Benar *</label>
                    <select name="correct_option" id="edit_correct_option" class="form-control" required style="font-weight: 700;">
                        <option value="a">A</option>
                        <option value="b">B</option>
                        <option value="c">C</option>
                        <option value="d">D</option>
                    </select>
                </div>
                <div>
                    <label class="form-label" for="edit_explanation" style="font-weight: 700;">Pembahasan (Opsional)</label>
                    <input type="text" name="explanation" id="edit_explanation" class="form-control">
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 0.5rem; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
                <button type="button" class="btn btn-secondary" onclick="closeEditQuestionModal()">Batal</button>
                <button type="submit" class="btn btn-primary" style="font-weight: 700;">💾 Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: EDIT PENGATURAN MODUL JEPANG ================= -->
<div id="editTestModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 9999; align-items: center; justify-content: center; padding: 1.5rem; backdrop-filter: blur(2px);">
    <div style="background: #ffffff; border-radius: var(--radius-lg); width: 100%; max-width: 580px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); overflow: hidden; max-height: 90vh; display: flex; flex-direction: column;">
        <div style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 1.15rem 1.5rem; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 1.15rem; margin: 0; font-weight: 800; color: #0f172a;">
                ⚙️ Atur Modul Bahasa Jepang
            </h3>
            <button type="button" onclick="closeEditTestModal()" style="background: none; border: none; font-size: 1.5rem; line-height: 1; color: #64748b; cursor: pointer;">&times;</button>
        </div>

        <form id="editTestForm" method="POST" style="margin: 0; overflow-y: auto; padding: 1.5rem; display: flex; flex-direction: column; gap: 1.1rem;">
            @csrf
            @method('PUT')

            <div>
                <label class="form-label" for="edit_test_title" style="font-weight: 700;">Judul Modul / Latihan *</label>
                <input type="text" name="title" id="edit_test_title" class="form-control" required>
            </div>

            <div>
                <label class="form-label" for="edit_test_desc" style="font-weight: 700;">Deskripsi (Opsional)</label>
                <textarea name="description" id="edit_test_desc" class="form-control" rows="2"></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                <div>
                    <label class="form-label" for="edit_test_duration" style="font-weight: 700;">Durasi (Menit) *</label>
                    <input type="number" name="duration_minutes" id="edit_test_duration" class="form-control" min="5" max="180" required>
                </div>
                <div>
                    <label class="form-label" for="edit_test_pass_score" style="font-weight: 700;">KKM Lulus (%) *</label>
                    <input type="number" name="pass_score" id="edit_test_pass_score" class="form-control" min="10" max="100" required>
                </div>
                <div>
                    <label class="form-label" for="edit_test_target_q" style="font-weight: 700;">Target Soal Acak *</label>
                    <input type="number" name="target_questions" id="edit_test_target_q" class="form-control" min="1" max="50" required>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 0.5rem; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
                <button type="button" class="btn btn-secondary" onclick="closeEditTestModal()">Batal</button>
                <button type="submit" class="btn btn-primary" style="font-weight: 700;">💾 Simpan Pengaturan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateQuestionModal(isJpTest, targetId) {
        document.getElementById('create_is_japanese_test').value = isJpTest ? '1' : '0';
        if (isJpTest) {
            document.getElementById('create_test_id').value = targetId;
            document.getElementById('create_level_id').value = '';
        } else {
            document.getElementById('create_level_id').value = targetId;
            document.getElementById('create_test_id').value = '';
        }
        document.getElementById('createQuestionModal').style.display = 'flex';
        document.getElementById('create_question').focus();
    }

    function closeCreateQuestionModal() {
        document.getElementById('createQuestionModal').style.display = 'none';
    }

    function openEditQuestionModal(q, isJpTest) {
        document.getElementById('editQuestionForm').action = '/superadmin/questions/' + q.id;
        document.getElementById('edit_is_japanese_test').value = isJpTest ? '1' : '0';
        document.getElementById('editModalHeaderTitle').textContent = '✏️ Edit Soal #' + q.question_number;
        document.getElementById('edit_question_number').value = q.question_number;
        document.getElementById('edit_points').value = q.points;
        document.getElementById('edit_question').value = q.question;
        document.getElementById('edit_option_a').value = q.option_a;
        document.getElementById('edit_option_b').value = q.option_b;
        document.getElementById('edit_option_c').value = q.option_c;
        document.getElementById('edit_option_d').value = q.option_d;
        document.getElementById('edit_correct_option').value = q.correct_option.toLowerCase();
        document.getElementById('edit_explanation').value = q.explanation || '';

        document.getElementById('editQuestionModal').style.display = 'flex';
        document.getElementById('edit_question').focus();
    }

    function closeEditQuestionModal() {
        document.getElementById('editQuestionModal').style.display = 'none';
    }

    function openEditTestModal(test) {
        document.getElementById('editTestForm').action = '/superadmin/questions/japanese/' + test.id + '/update-test';
        document.getElementById('edit_test_title').value = test.title;
        document.getElementById('edit_test_desc').value = test.description || '';
        document.getElementById('edit_test_duration').value = test.duration_minutes;
        document.getElementById('edit_test_pass_score').value = test.pass_score;
        document.getElementById('edit_test_target_q').value = test.target_questions || (test.category === 'per_4_pertemuan' ? 15 : 10);
        document.getElementById('editTestModal').style.display = 'flex';
    }

    function closeEditTestModal() {
        document.getElementById('editTestModal').style.display = 'none';
    }

    window.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeCreateQuestionModal();
            closeEditQuestionModal();
            closeEditTestModal();
        }
    });

    document.getElementById('createQuestionModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeCreateQuestionModal();
    });

    document.getElementById('editQuestionModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeEditQuestionModal();
    });

    document.getElementById('editTestModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeEditTestModal();
    });
</script>
@endsection
