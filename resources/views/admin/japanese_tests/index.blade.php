@extends('layouts.app')

@section('title', 'Kelola Latihan Soal & Evaluasi Bahasa Jepang - Guru Musashi')

@section('content')
<div style="margin-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                <span class="badge" style="background: #fdf2f8; color: #db2777; font-weight: 800;">
                    🇯🇵 Guru / Admin Bahasa Jepang
                </span>
                <span class="badge badge-primary">
                    12 Pertemuan Belajar
                </span>
                <span class="badge" style="background: #ecfdf5; color: #047857; font-weight: 800;">
                    15 Modul (165 Soal)
                </span>
            </div>
            <h1 style="font-size: 1.85rem; margin: 0; font-weight: 800; color: #0f172a;">
                Kelola Latihan Soal & Ujian Evaluasi Bahasa Jepang
            </h1>
            <p style="color: var(--text-muted); font-size: 0.92rem; margin-top: 4px;">
                Kelola Tes Evaluasi Siswa (Per 4 Pertemuan) dan Latihan Soal Mandiri Per Pertemuan (10 nomor acak).
                <strong style="color: #db2777;">Siswa hanya dapat mengakses dan mengerjakan soal setelah Anda mengaktifkannya.</strong>
            </p>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('admin.materials.index') }}" class="btn btn-secondary btn-sm" style="font-weight: 700;">
                📚 Modul Materi Jepang
            </a>
            <a href="{{ route('admin.meetings.index') }}" class="btn btn-secondary btn-sm" style="font-weight: 700;">
                🗓️ Kelola Pertemuan
            </a>
        </div>
    </div>
</div>

<!-- Bulk Activation Controls Bar -->
<div class="card" style="margin-bottom: 1.75rem; border: 1px solid #fbcfe8; background: linear-gradient(135deg, #fff1f2 0%, #fdf2f8 100%); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
    <div class="card-body" style="padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="font-size: 1.8rem; background: #ffffff; width: 46px; height: 46px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: var(--shadow-sm);">
                ⚡
            </div>
            <div>
                <strong style="color: #881337; font-size: 1rem; display: block;">Aksi Cepat Aktivasi Soal Siswa (1-Klik)</strong>
                <span style="font-size: 0.82rem; color: #9f1239;">Aktifkan atau kunci seluruh modul soal sekaligus untuk siswa kelas Jepang.</span>
            </div>
        </div>

        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <!-- Bulk Toggle Meeting Tests -->
            <form action="{{ route('admin.japanese.tests.toggle-all') }}" method="POST" style="margin: 0; display: inline;">
                @csrf
                <input type="hidden" name="action" value="activate">
                <input type="hidden" name="category" value="per_pertemuan">
                <button type="submit" class="btn btn-sm" style="background: #059669; color: #ffffff; font-weight: 700; border: none; padding: 7px 12px;" onclick="return confirm('Aktifkan SEMUA 12 Latihan Per Pertemuan untuk siswa?')">
                    ▶ Aktifkan Semua Latihan (1-12)
                </button>
            </form>

            <form action="{{ route('admin.japanese.tests.toggle-all') }}" method="POST" style="margin: 0; display: inline;">
                @csrf
                <input type="hidden" name="action" value="deactivate">
                <input type="hidden" name="category" value="per_pertemuan">
                <button type="submit" class="btn btn-sm" style="background: #e2e8f0; color: #334155; font-weight: 700; border: 1px solid #cbd5e1; padding: 7px 12px;" onclick="return confirm('Kunci/Nonaktifkan SEMUA 12 Latihan Per Pertemuan?')">
                    ⏸ Kunci Semua Latihan
                </button>
            </form>

            <!-- Bulk Toggle Periodic Tests -->
            <form action="{{ route('admin.japanese.tests.toggle-all') }}" method="POST" style="margin: 0; display: inline;">
                @csrf
                <input type="hidden" name="action" value="activate">
                <input type="hidden" name="category" value="per_4_pertemuan">
                <button type="submit" class="btn btn-sm" style="background: #db2777; color: #ffffff; font-weight: 700; border: none; padding: 7px 12px;" onclick="return confirm('Aktifkan SEMUA 3 Ujian Evaluasi Per 4 Pertemuan?')">
                    ▶ Aktifkan Semua Evaluasi
                </button>
            </form>

            <form action="{{ route('admin.japanese.tests.toggle-all') }}" method="POST" style="margin: 0; display: inline;">
                @csrf
                <input type="hidden" name="action" value="deactivate">
                <input type="hidden" name="category" value="per_4_pertemuan">
                <button type="submit" class="btn btn-sm" style="background: #e2e8f0; color: #334155; font-weight: 700; border: 1px solid #cbd5e1; padding: 7px 12px;" onclick="return confirm('Kunci/Nonaktifkan SEMUA 3 Ujian Evaluasi?')">
                    ⏸ Kunci Semua Evaluasi
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Tabs Navigation -->
<div style="display: flex; gap: 10px; margin-bottom: 1.5rem; border-bottom: 2px solid #e2e8f0; padding-bottom: 4px;">
    <button type="button" id="adminTabMeetingBtn" onclick="switchAdminTab('meeting')" style="padding: 10px 18px; font-weight: 800; font-size: 0.95rem; border: none; background: none; border-bottom: 3px solid #db2777; color: #db2777; cursor: pointer; display: flex; align-items: center; gap: 8px;">
        <span>📝 Latihan Soal Per Pertemuan</span>
        <span class="badge" style="background: #fdf2f8; color: #be185d; font-size: 0.75rem;">12 Pertemuan (10 Soal Acak)</span>
    </button>
    <button type="button" id="adminTabPeriodicBtn" onclick="switchAdminTab('periodic')" style="padding: 10px 18px; font-weight: 800; font-size: 0.95rem; border: none; background: none; border-bottom: 3px solid transparent; color: #64748b; cursor: pointer; display: flex; align-items: center; gap: 8px;">
        <span>🏆 Ujian Evaluasi Per 4 Pertemuan</span>
        <span class="badge badge-neutral" style="font-size: 0.75rem;">3 Modul (15 Soal Acak)</span>
    </button>
    <button type="button" id="adminTabSubmissionsBtn" onclick="switchAdminTab('submissions')" style="padding: 10px 18px; font-weight: 800; font-size: 0.95rem; border: none; background: none; border-bottom: 3px solid transparent; color: #64748b; cursor: pointer; display: flex; align-items: center; gap: 8px;">
        <span>📊 Rekap Nilai Siswa Jepang</span>
        <span class="badge badge-primary" style="font-size: 0.75rem;">{{ $submissions->total() }} Data</span>
    </button>
</div>

<!-- Tab 1: Latihan Soal Per Pertemuan (12 Pertemuan) -->
<div id="adminTabMeetingContent">
    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: var(--radius-md); padding: 10px 16px; margin-bottom: 1.25rem; display: flex; justify-content: space-between; align-items: center;">
        <span style="font-size: 0.88rem; color: #475569;">
            💡 <strong>Info:</strong> Setiap pertemuan menyajikan <strong>10 nomor soal acak</strong> dari bank soal. Guru dapat mengaktifkan latihan ini agar siswa dapat mengerjakan di kelas.
        </span>
        <span class="badge badge-neutral" style="font-weight: 700;">12 Pertemuan</span>
    </div>

    <div class="grid grid-cols-3" style="gap: 1.25rem; margin-bottom: 2.5rem;">
        @foreach($meetingTests as $t)
            <div class="card" style="box-shadow: var(--shadow-sm); border-radius: var(--radius-lg); border-top: 4px solid {{ $t->is_active ? '#10b981' : '#94a3b8' }}; display: flex; flex-direction: column; justify-content: space-between; background: {{ $t->is_active ? '#ffffff' : '#fcfcfd' }};">
                <div class="card-body" style="padding: 1.25rem 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px; gap: 8px;">
                        <div>
                            <span class="badge" style="background: #fdf2f8; color: #be185d; font-weight: 800; font-size: 0.78rem;">
                                🗓️ Pertemuan {{ $t->start_meeting }}
                            </span>
                        </div>
                        <div>
                            @if($t->is_active)
                                <span class="badge" style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; font-weight: 800; font-size: 0.72rem;">
                                    🟢 Aktif di Siswa
                                </span>
                            @else
                                <span class="badge" style="background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1; font-weight: 700; font-size: 0.72rem;">
                                    ⚪ Terkunci (Nonaktif)
                                </span>
                            @endif
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 6px;">
                        <h3 style="font-size: 1.05rem; margin: 0; color: #0f172a; font-weight: 800; line-height: 1.35;">
                            {{ $t->title }}
                        </h3>
                        <button type="button" class="btn btn-secondary btn-sm" onclick='openEditTestModal(@json($t))' title="Edit Judul & Pengaturan Soal" style="padding: 2px 8px; font-size: 0.75rem; white-space: nowrap; margin-left: 6px;">
                            ⚙️ Atur
                        </button>
                    </div>

                    <p style="font-size: 0.82rem; color: var(--text-muted); line-height: 1.4; margin-bottom: 0.85rem; min-height: 2.8em;">
                        {{ $t->description }}
                    </p>

                    @if($t->is_active)
                        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; font-size: 0.76rem; padding: 5px 10px; border-radius: 6px; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 6px;">
                            <span>🌐</span>
                            <span><strong>Status Aktif:</strong> Siswa dapat mengerjakan 10 soal acak pertemuan ini.</span>
                        </div>
                    @else
                        <div style="background: #fffbeb; border: 1px solid #fde68a; color: #92400e; font-size: 0.76rem; padding: 5px 10px; border-radius: 6px; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 6px;">
                            <span>🔒</span>
                            <span><strong>Terkunci:</strong> Disembunyikan agar siswa tidak membuka soal sebelum guru mulai.</span>
                        </div>
                    @endif

                    <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 8px 12px; display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 0.35rem;">
                        <span>Target Soal Acak:</span>
                        <strong style="color: #db2777;">{{ $t->target_questions ?: 10 }} Soal / Pengerjaan</strong>
                    </div>
                    <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 8px 12px; display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 0.35rem;">
                        <span>Durasi & KKM:</span>
                        <span>⏱️ {{ $t->duration_minutes }}m | KKM {{ $t->pass_score }}%</span>
                    </div>
                    <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 8px 12px; display: flex; justify-content: space-between; font-size: 0.8rem;">
                        <span>Total Bank Soal & Siswa:</span>
                        <strong>{{ $t->questions_count }} Soal | {{ $t->submissions_count }} Siswa</strong>
                    </div>
                </div>

                <div class="card-footer" style="background: #ffffff; padding: 0.85rem 1.25rem; border-top: 1px solid var(--border-color); display: flex; flex-direction: column; gap: 6px;">
                    <div style="display: flex; gap: 6px;">
                        <a href="{{ route('admin.japanese.tests.questions', $t) }}" class="btn btn-primary btn-sm" style="flex: 1; text-align: center; font-weight: 700; background: #db2777; border-color: #db2777; font-size: 0.82rem;">
                            ✏️ Bank Soal ({{ $t->questions_count }})
                        </a>
                    </div>

                    <!-- Individual Activation Toggle -->
                    <form action="{{ route('admin.japanese.tests.toggle-active', $t) }}" method="POST" style="margin: 0;">
                        @csrf
                        @if($t->is_active)
                            <button type="submit" class="btn btn-sm btn-secondary" style="width: 100%; font-weight: 700; color: #b91c1c; background: #fef2f2; border: 1px solid #fecaca; display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 0.8rem;" onclick="return confirm('Kunci/Nonaktifkan latihan pertemuan {{ $t->start_meeting }}?')">
                                <span>⏸</span> Kunci (Sembunyikan dari Siswa)
                            </button>
                        @else
                            <button type="submit" class="btn btn-sm" style="width: 100%; font-weight: 700; color: #ffffff; background: #059669; border: 1px solid #059669; display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 0.8rem;">
                                <span>▶</span> Aktifkan untuk Siswa
                            </button>
                        @endif
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Tab 2: Ujian Evaluasi Per 4 Pertemuan (3 Modul) -->
<div id="adminTabPeriodicContent" style="display: none;">
    <div style="background: #fdf2f8; border: 1px solid #fbcfe8; border-radius: var(--radius-md); padding: 10px 16px; margin-bottom: 1.25rem; display: flex; justify-content: space-between; align-items: center;">
        <span style="font-size: 0.88rem; color: #831843;">
            🏆 <strong>Info Ujian Berkala:</strong> Setiap evaluasi menyajikan <strong>15 nomor acak</strong> yang merangkum 4 pertemuan belajar sekaligus.
        </span>
        <span class="badge" style="background: #be185d; color: #ffffff; font-weight: 700;">3 Evaluasi Berkala</span>
    </div>

    <div class="grid grid-cols-3" style="gap: 1.25rem; margin-bottom: 2.5rem;">
        @foreach($periodicTests as $t)
            <div class="card" style="box-shadow: var(--shadow-sm); border-radius: var(--radius-lg); border-top: 4px solid {{ $t->is_active ? '#10b981' : '#94a3b8' }}; display: flex; flex-direction: column; justify-content: space-between; background: {{ $t->is_active ? '#ffffff' : '#fcfcfd' }};">
                <div class="card-body" style="padding: 1.25rem 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px; gap: 8px;">
                        <div>
                            <span class="badge" style="background: #fdf2f8; color: #be185d; font-weight: 800; font-size: 0.78rem;">
                                🗓️ Pertemuan {{ $t->start_meeting }} - {{ $t->end_meeting }}
                            </span>
                        </div>
                        <div>
                            @if($t->is_active)
                                <span class="badge" style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; font-weight: 800; font-size: 0.72rem;">
                                    🟢 Aktif di Siswa
                                </span>
                            @else
                                <span class="badge" style="background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1; font-weight: 700; font-size: 0.72rem;">
                                    ⚪ Terkunci (Nonaktif)
                                </span>
                            @endif
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 6px;">
                        <h3 style="font-size: 1.15rem; margin: 0; color: #0f172a; font-weight: 800; line-height: 1.35;">
                            {{ $t->title }}
                        </h3>
                        <button type="button" class="btn btn-secondary btn-sm" onclick='openEditTestModal(@json($t))' title="Edit Judul & Pengaturan Ujian" style="padding: 2px 8px; font-size: 0.75rem; white-space: nowrap; margin-left: 6px;">
                            ⚙️ Atur
                        </button>
                    </div>

                    <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.45; margin-bottom: 0.85rem;">
                        {{ $t->description }}
                    </p>

                    @if($t->is_active)
                        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; font-size: 0.78rem; padding: 6px 10px; border-radius: 6px; margin-bottom: 0.85rem; display: flex; align-items: center; gap: 6px;">
                            <span>🌐</span>
                            <span><strong>Status Aktif:</strong> Siswa kelas Jepang dapat melihat dan mengerjakan evaluasi ini (15 soal acak).</span>
                        </div>
                    @else
                        <div style="background: #fffbeb; border: 1px solid #fde68a; color: #92400e; font-size: 0.78rem; padding: 6px 10px; border-radius: 6px; margin-bottom: 0.85rem; display: flex; align-items: center; gap: 6px;">
                            <span>🔒</span>
                            <span><strong>Status Terkunci:</strong> Disembunyikan dari siswa agar tidak bisa dikerjakan sebelum waktunya.</span>
                        </div>
                    @endif

                    <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 8px 12px; display: flex; justify-content: space-between; font-size: 0.82rem; margin-bottom: 0.4rem;">
                        <span>Target Soal Evaluasi:</span>
                        <strong style="color: #db2777; font-size: 0.92rem;">{{ $t->target_questions ?: 15 }} Soal Acak</strong>
                    </div>
                    <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 8px 12px; display: flex; justify-content: space-between; font-size: 0.82rem; margin-bottom: 0.4rem;">
                        <span>Durasi & Standar Kelulusan:</span>
                        <strong style="font-size: 0.84rem;">⏱️ {{ $t->duration_minutes }}m | KKM {{ $t->pass_score }}%</strong>
                    </div>
                    <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 8px 12px; display: flex; justify-content: space-between; font-size: 0.82rem;">
                        <span>Total Bank & Mengerjakan:</span>
                        <strong>{{ $t->questions_count }} Soal | {{ $t->submissions_count }} Siswa</strong>
                    </div>
                </div>

                <div class="card-footer" style="background: #ffffff; padding: 1rem 1.5rem; border-top: 1px solid var(--border-color); display: flex; flex-direction: column; gap: 8px;">
                    <div style="display: flex; gap: 8px;">
                        <a href="{{ route('admin.japanese.tests.questions', $t) }}" class="btn btn-primary btn-sm" style="flex: 1; text-align: center; font-weight: 700; background: #db2777; border-color: #db2777;">
                            ✏️ Bank Soal ({{ $t->questions_count }})
                        </a>
                    </div>

                    <!-- Activation Toggle Button -->
                    <form action="{{ route('admin.japanese.tests.toggle-active', $t) }}" method="POST" style="margin: 0;">
                        @csrf
                        @if($t->is_active)
                            <button type="submit" class="btn btn-sm btn-secondary" style="width: 100%; font-weight: 700; color: #b91c1c; background: #fef2f2; border: 1px solid #fecaca; display: flex; align-items: center; justify-content: center; gap: 6px;" onclick="return confirm('Nonaktifkan tes {{ $t->title }}? Ujian akan disembunyikan dari siswa jepang.')">
                                <span>⏸</span> Nonaktifkan (Kunci dari Siswa)
                            </button>
                        @else
                            <button type="submit" class="btn btn-sm" style="width: 100%; font-weight: 700; color: #ffffff; background: #059669; border: 1px solid #059669; display: flex; align-items: center; justify-content: center; gap: 6px;">
                                <span>▶</span> Aktifkan Ujian untuk Siswa
                            </button>
                        @endif
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Tab 3: Rekap Perolehan Nilai Siswa -->
<div id="adminTabSubmissionsContent" style="display: none;">
    <div class="card" style="box-shadow: var(--shadow-sm); border-radius: var(--radius-lg); overflow: hidden;">
        <div class="card-header" style="background: #ffffff; padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div>
                <h2 style="font-size: 1.25rem; margin: 0; font-weight: 800; color: #0f172a;">
                    📊 Rekap Perolehan Nilai Siswa Bahasa Jepang
                </h2>
                <p style="font-size: 0.84rem; color: var(--text-muted); margin: 2px 0 0;">
                    Hasil pengerjaan latihan pertemuan (10 soal) dan evaluasi per 4 pertemuan (15 soal) oleh siswa.
                </p>
            </div>

            <form method="GET" action="{{ route('admin.japanese.tests.index') }}" style="display: flex; gap: 8px;">
                <select name="test_id" class="form-control form-control-sm" onchange="this.form.submit()" style="font-weight: 600;">
                    <option value="all">Semua Modul Soal</option>
                    <optgroup label="-- Latihan Per Pertemuan (10 Soal) --">
                        @foreach($meetingTests as $t)
                            <option value="{{ $t->id }}" {{ $selectedTestId == $t->id ? 'selected' : '' }}>
                                {{ $t->title }}
                            </option>
                        @endforeach
                    </optgroup>
                    <optgroup label="-- Evaluasi Per 4 Pertemuan (15 Soal) --">
                        @foreach($periodicTests as $t)
                            <option value="{{ $t->id }}" {{ $selectedTestId == $t->id ? 'selected' : '' }}>
                                {{ $t->title }}
                            </option>
                        @endforeach
                    </optgroup>
                </select>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table" style="vertical-align: middle;">
                <thead>
                    <tr style="background: #f8fafc; font-size: 0.84rem;">
                        <th style="width: 50px; text-align: center;">No</th>
                        <th>Nama Siswa</th>
                        <th>Kelas Jepang</th>
                        <th>Judul Modul Soal</th>
                        <th style="text-align: center;">Benar / Total</th>
                        <th style="text-align: center;">Nilai Akhir</th>
                        <th style="text-align: center;">Status</th>
                        <th>Waktu Pengerjaan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($submissions as $idx => $sub)
                        <tr>
                            <td style="text-align: center; font-weight: 700; color: #64748b;">
                                {{ $submissions->firstItem() + $idx }}
                            </td>
                            <td>
                                <strong style="color: #0f172a; font-size: 0.95rem;">{{ $sub->student?->name ?? 'Siswa' }}</strong>
                                <div style="font-size: 0.74rem; color: var(--text-muted);">{{ $sub->student?->email }}</div>
                            </td>
                            <td>
                                <span class="badge" style="background: #fdf2f8; color: #be185d; font-weight: 800; font-size: 0.82rem;">
                                    {{ $sub->student?->class_name ?? 'Kelas 1' }}
                                </span>
                            </td>
                            <td>
                                <span style="font-weight: 600; font-size: 0.88rem; color: #1e293b;">
                                    {{ $sub->test?->title }}
                                </span>
                                <div style="font-size: 0.75rem; color: #64748b;">
                                    {{ $sub->test?->category === 'per_pertemuan' ? 'Latihan Pertemuan ' . $sub->test?->start_meeting : 'Evaluasi Pertemuan ' . $sub->test?->start_meeting . '-' . $sub->test?->end_meeting }}
                                </div>
                            </td>
                            <td style="text-align: center; font-weight: 700;">
                                {{ $sub->correct_count }} / {{ $sub->total_questions }}
                            </td>
                            <td style="text-align: center;">
                                <span style="font-size: 1.25rem; font-weight: 800; color: {{ $sub->score >= 70 ? '#10b981' : '#ef4444' }}; font-family: monospace;">
                                    {{ $sub->score }}
                                </span>
                            </td>
                            <td style="text-align: center;">
                                @if($sub->is_passed)
                                    <span class="badge badge-success" style="font-size: 0.8rem; font-weight: 700;">
                                        ✅ Lulus
                                    </span>
                                @else
                                    <span class="badge badge-danger" style="font-size: 0.8rem; font-weight: 700;">
                                        ⚠️ Perlu Remidial
                                    </span>
                                @endif
                            </td>
                            <td style="font-size: 0.82rem; color: #64748b;">
                                {{ $sub->created_at->format('d M Y, H:i') }} WIB
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 2.5rem; color: var(--text-muted);">
                                Belum ada siswa yang mengerjakan latihan atau evaluasi ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($submissions->hasPages())
            <div class="card-footer" style="background: #ffffff; padding: 1rem 1.5rem;">
                {{ $submissions->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Edit Pengaturan Ujian -->
<div id="editTestModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; padding: 1rem; overflow-y: auto;">
    <div style="background: #ffffff; border-radius: var(--radius-lg); max-width: 540px; width: 100%; box-shadow: var(--shadow-xl); overflow: hidden; margin: 2rem auto;">
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 1.2rem; margin: 0; color: #0f172a; font-weight: 800;">⚙️ Edit Pengaturan Modul Soal</h3>
            <button type="button" onclick="closeEditTestModal()" style="background:none; border:none; font-size: 1.4rem; cursor: pointer;">&times;</button>
        </div>

        <form id="editTestForm" action="" method="POST">
            @csrf
            @method('PUT')
            <div style="padding: 1.5rem;">
                <div class="form-group">
                    <label class="form-label" style="font-weight: 700;">Judul Modul Soal *</label>
                    <input type="text" name="title" id="modal_test_title" class="form-control" required placeholder="Contoh: Latihan Soal Bahasa Jepang - Pertemuan 1">
                </div>

                <div class="form-group">
                    <label class="form-label" style="font-weight: 700;">Deskripsi & Cakupan Materi</label>
                    <textarea name="description" id="modal_test_description" class="form-control" rows="3" placeholder="Jelaskan kisi-kisi atau cakupan materi yang diujikan..."></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 700; font-size: 0.85rem;">Soal Acak (Butir) *</label>
                        <input type="number" name="target_questions" id="modal_test_target_questions" class="form-control" min="1" max="50" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="font-weight: 700; font-size: 0.85rem;">Durasi (Menit) *</label>
                        <input type="number" name="duration_minutes" id="modal_test_duration" class="form-control" min="5" max="180" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="font-weight: 700; font-size: 0.85rem;">KKM (%) *</label>
                        <input type="number" name="pass_score" id="modal_test_pass_score" class="form-control" min="10" max="100" required>
                    </div>
                </div>
            </div>

            <div style="padding: 1rem 1.5rem; background: #f8fafc; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary" onclick="closeEditTestModal()">Batal</button>
                <button type="submit" class="btn btn-primary" style="font-weight: 700; background: #db2777; border-color: #db2777;">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function switchAdminTab(tab) {
    const meetingContent = document.getElementById('adminTabMeetingContent');
    const periodicContent = document.getElementById('adminTabPeriodicContent');
    const submissionsContent = document.getElementById('adminTabSubmissionsContent');

    const tabMeetingBtn = document.getElementById('adminTabMeetingBtn');
    const tabPeriodicBtn = document.getElementById('adminTabPeriodicBtn');
    const tabSubmissionsBtn = document.getElementById('adminTabSubmissionsBtn');

    meetingContent.style.display = 'none';
    periodicContent.style.display = 'none';
    submissionsContent.style.display = 'none';

    tabMeetingBtn.style.borderBottomColor = 'transparent';
    tabMeetingBtn.style.color = '#64748b';
    tabPeriodicBtn.style.borderBottomColor = 'transparent';
    tabPeriodicBtn.style.color = '#64748b';
    tabSubmissionsBtn.style.borderBottomColor = 'transparent';
    tabSubmissionsBtn.style.color = '#64748b';

    if (tab === 'meeting') {
        meetingContent.style.display = 'block';
        tabMeetingBtn.style.borderBottomColor = '#db2777';
        tabMeetingBtn.style.color = '#db2777';
    } else if (tab === 'periodic') {
        periodicContent.style.display = 'block';
        tabPeriodicBtn.style.borderBottomColor = '#db2777';
        tabPeriodicBtn.style.color = '#db2777';
    } else {
        submissionsContent.style.display = 'block';
        tabSubmissionsBtn.style.borderBottomColor = '#db2777';
        tabSubmissionsBtn.style.color = '#db2777';
    }
}

function openEditTestModal(test) {
    document.getElementById('modal_test_title').value = test.title;
    document.getElementById('modal_test_description').value = test.description || '';
    document.getElementById('modal_test_target_questions').value = test.target_questions || (test.category === 'per_4_pertemuan' ? 15 : 10);
    document.getElementById('modal_test_duration').value = test.duration_minutes;
    document.getElementById('modal_test_pass_score').value = test.pass_score;
    document.getElementById('editTestForm').action = "{{ url('admin/japanese-tests') }}/" + test.id;
    document.getElementById('editTestModal').style.display = 'flex';
}

function closeEditTestModal() {
    document.getElementById('editTestModal').style.display = 'none';
}
</script>
@endpush
@endsection
