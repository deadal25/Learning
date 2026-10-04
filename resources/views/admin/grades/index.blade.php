@extends('layouts.app')

@section('title', 'Kelola Nilai & Feedback Siswa - Guru Bahasa Inggris')

@section('content')
<div style="margin-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div class="badge badge-primary" style="margin-bottom: 6px;">
                ✨ Khusus Guru Bahasa Inggris
            </div>
            <h1 style="font-size: 1.85rem; margin: 0; font-weight: 800; color: #0f172a;">
                Kelola Nilai & Evaluasi (Monthly Feedback)
            </h1>
            <p style="color: var(--text-muted); font-size: 0.92rem; margin-top: 4px;">
                Input manual nilai pertemuan, skor ujian kemampuan bahasa, serta catatan feedback evaluasi untuk setiap siswa per kelas dan periode bulan mengajar (Bulan 1 - 5).
            </p>
        </div>
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <!-- Direct Download Single Month Excel Button -->
            <a href="{{ route('admin.grades.export', ['class_name' => $selectedClass, 'month' => $selectedMonth]) }}" 
               class="btn btn-success" 
               style="background: #10b981; border-color: #10b981; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(16,185,129,0.3); padding: 8px 16px; font-size: 0.9rem;"
               title="Unduh format Excel untuk kelas {{ $selectedClass }} di Bulan {{ $selectedMonth }}">
                <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                📥 Unduh Excel Bulan {{ $selectedMonth }}
            </a>

            <!-- Direct Download All Months (1-5) Excel Button -->
            <a href="{{ route('admin.grades.export', ['class_name' => $selectedClass, 'month' => 'all']) }}" 
               class="btn" 
               style="background: #8b5cf6; color: #ffffff; border-color: #8b5cf6; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(139,92,246,0.3); padding: 8px 16px; font-size: 0.9rem;"
               title="Unduh seluruh rekap nilai Bulan 1 sampai Bulan 5 untuk kelas {{ $selectedClass }}">
                <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                📚 Unduh Semua Bulan (Bulan 1 - 5)
            </a>

            <!-- Dropdown Options for More Export Choices -->
            <div style="position: relative; display: inline-block;" id="downloadDropdownContainer">
                <button type="button" class="btn btn-secondary btn-sm" onclick="toggleDownloadDropdown()" style="font-weight: 700; padding: 9px 14px; display: inline-flex; align-items: center; gap: 6px;">
                    ⚙️ Opsi Unduh ▾
                </button>
                <div id="downloadDropdownMenu" style="display: none; position: absolute; right: 0; top: 100%; margin-top: 6px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 10px; box-shadow: 0 10px 25px rgba(0,0,0,0.18); width: 290px; z-index: 1000; overflow: hidden; font-size: 0.86rem; text-align: left;">
                    <div style="padding: 10px 14px; background: #f8fafc; font-weight: 800; font-size: 0.74rem; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; letter-spacing: 0.5px;">
                        📌 Kelas Terpilih ({{ $selectedClass }})
                    </div>
                    <a href="{{ route('admin.grades.export', ['class_name' => $selectedClass, 'month' => $selectedMonth]) }}" class="download-menu-item" style="display: flex; align-items: center; gap: 8px; padding: 10px 14px; color: #1e293b; text-decoration: none; border-bottom: 1px solid #f1f5f9; transition: background 0.15s;">
                        <span style="font-size: 1.1rem;">📄</span>
                        <div>
                            <div style="font-weight: 700;">Unduh Bulan {{ $selectedMonth }}</div>
                            <div style="font-size: 0.76rem; color: #64748b;">Khusus kelas {{ $selectedClass }}</div>
                        </div>
                    </a>
                    <a href="{{ route('admin.grades.export', ['class_name' => $selectedClass, 'month' => 'all']) }}" class="download-menu-item" style="display: flex; align-items: center; gap: 8px; padding: 10px 14px; color: #1e293b; text-decoration: none; border-bottom: 1px solid #e2e8f0; transition: background 0.15s;">
                        <span style="font-size: 1.1rem;">📚</span>
                        <div>
                            <div style="font-weight: 700;">Unduh Semua Bulan (1 s/d 5)</div>
                            <div style="font-size: 0.76rem; color: #64748b;">Lengkap Bulan 1 - 5 kelas {{ $selectedClass }}</div>
                        </div>
                    </a>

                    <div style="padding: 10px 14px; background: #f8fafc; font-weight: 800; font-size: 0.74rem; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; letter-spacing: 0.5px;">
                        🌐 Semua Sheet Kelas
                    </div>
                    <a href="{{ route('admin.grades.export', ['class_name' => 'all', 'month' => $selectedMonth]) }}" class="download-menu-item" style="display: flex; align-items: center; gap: 8px; padding: 10px 14px; color: #1e293b; text-decoration: none; border-bottom: 1px solid #f1f5f9; transition: background 0.15s;">
                        <span style="font-size: 1.1rem;">🌐</span>
                        <div>
                            <div style="font-weight: 700;">Unduh Bulan {{ $selectedMonth }} (Semua Kelas)</div>
                            <div style="font-size: 0.76rem; color: #64748b;">Semua sheet kelas untuk Bulan {{ $selectedMonth }}</div>
                        </div>
                    </a>
                    <a href="{{ route('admin.grades.export', ['class_name' => 'all', 'month' => 'all']) }}" class="download-menu-item" style="display: flex; align-items: center; gap: 8px; padding: 10px 14px; color: #1e293b; text-decoration: none; transition: background 0.15s;">
                        <span style="font-size: 1.1rem;">📦</span>
                        <div>
                            <div style="font-weight: 700;">Unduh Master Excel (Semua Bulan & Kelas)</div>
                            <div style="font-size: 0.76rem; color: #64748b;">Seluruh bulan 1 - 5 untuk seluruh sheet kelas</div>
                        </div>
                    </a>
                </div>
            </div>

            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary btn-sm" style="padding: 9px 14px;">
                &larr; Dashboard
            </a>
        </div>
    </div>
</div>

<!-- Class & Month Filter Card -->
<div class="card" style="margin-bottom: 2rem; box-shadow: var(--shadow-sm); border: 1px solid #e2e8f0;">
    <div class="card-body" style="padding: 1.25rem 1.5rem;">
        <form action="{{ route('admin.grades.index') }}" method="GET" style="display: flex; gap: 16px; align-items: flex-end; flex-wrap: wrap;">
            <div style="flex: 1.2; min-width: 240px;">
                <label class="form-label" for="filter_class" style="font-size: 0.85rem; font-weight: 700; color: #1e293b; margin-bottom: 4px;">
                    🏫 Pilih Kelas Bahasa Inggris:
                </label>
                <select name="class_name" id="filter_class" class="form-control" onchange="this.form.submit()" style="font-weight: 700; font-size: 0.95rem; border: 2px solid #cbd5e1;">
                    @foreach($classes as $c)
                        <option value="{{ $c->name }}" {{ $selectedClass === $c->name ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="flex: 1; min-width: 220px;">
                <label class="form-label" for="filter_month" style="font-size: 0.85rem; font-weight: 700; color: #1e293b; margin-bottom: 4px;">
                    🗓️ Periode Bulan Mengajar:
                </label>
                <select name="month" id="filter_month" class="form-control" onchange="this.form.submit()" style="font-weight: 700; font-size: 0.95rem; border: 2px solid #cbd5e1;">
                    <option value="1" {{ ($selectedMonth ?? $selectedWeek) == 1 ? 'selected' : '' }}>1ST MONTH (Bulan Ke-1)</option>
                    <option value="2" {{ ($selectedMonth ?? $selectedWeek) == 2 ? 'selected' : '' }}>2ND MONTH (Bulan Ke-2)</option>
                    <option value="3" {{ ($selectedMonth ?? $selectedWeek) == 3 ? 'selected' : '' }}>3RD MONTH (Bulan Ke-3)</option>
                    <option value="4" {{ ($selectedMonth ?? $selectedWeek) == 4 ? 'selected' : '' }}>4TH MONTH (Bulan Ke-4)</option>
                    <option value="5" {{ ($selectedMonth ?? $selectedWeek) == 5 ? 'selected' : '' }}>5TH MONTH (Bulan Ke-5)</option>
                </select>
            </div>

            <div>
                <button type="submit" class="btn btn-primary" style="font-weight: 700; padding: 10px 22px;">
                    Buka Lembar Nilai
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Stats Bar -->
<div class="grid grid-cols-4" style="margin-bottom: 2rem;">
    <div class="card" style="border-left: 4px solid #3b82f6;">
        <div class="card-body" style="padding: 1.25rem;">
            <div style="font-size: 0.78rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Kelas Aktif</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: #0f172a; margin-top: 4px;">
                {{ $selectedClass }}
            </div>
            <div style="font-size: 0.8rem; color: #2563eb; font-weight: 600; margin-top: 2px;">Bulan Ke-{{ $selectedMonth ?? $selectedWeek }}</div>
        </div>
    </div>

    <div class="card" style="border-left: 4px solid #8b5cf6;">
        <div class="card-body" style="padding: 1.25rem;">
            <div style="font-size: 0.78rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Total Siswa di Kelas</div>
            <div style="font-size: 1.8rem; font-weight: 800; color: #0f172a; margin-top: 4px;">
                {{ $students->count() }} <span style="font-size: 0.85rem; font-weight: 500; color: var(--text-muted);">Siswa</span>
            </div>
            <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 2px;">Terdaftar di {{ $selectedClass }}</div>
        </div>
    </div>

    <div class="card" style="border-left: 4px solid #10b981;">
        <div class="card-body" style="padding: 1.25rem;">
            <div style="font-size: 0.78rem; font-weight: 700; color: #065f46; text-transform: uppercase;">Sudah Dinilai</div>
            <div style="font-size: 1.8rem; font-weight: 800; color: #10b981; margin-top: 4px;">
                {{ $totalEvaluated }} <span style="font-size: 0.85rem; font-weight: 500; color: var(--text-muted);">/ {{ $students->count() }}</span>
            </div>
            <div style="font-size: 0.78rem; color: #059669; margin-top: 2px;">Telah diinput nilai / feedback</div>
        </div>
    </div>

    <div class="card" style="border-left: 4px solid #f59e0b;">
        <div class="card-body" style="padding: 1.25rem;">
            <div style="font-size: 0.78rem; font-weight: 700; color: #b45309; text-transform: uppercase;">Rata-Rata Nilai Akhir</div>
            <div style="font-size: 1.8rem; font-weight: 800; color: #d97706; margin-top: 4px;">
                {{ $averageFinalScore }} <span style="font-size: 0.85rem; font-weight: 500; color: var(--text-muted);">PTS</span>
            </div>
            <div style="font-size: 0.78rem; color: #b45309; margin-top: 2px;">Final score rata-rata kelas</div>
        </div>
    </div>
</div>

<!-- Main Grade Sheet Form -->
<form action="{{ route('admin.grades.save') }}" method="POST" id="gradeSheetForm">
    @csrf
    <input type="hidden" name="class_name" value="{{ $selectedClass }}">
    <input type="hidden" name="month" value="{{ $selectedMonth ?? $selectedWeek }}">
    <input type="hidden" name="week" value="{{ $selectedMonth ?? $selectedWeek }}">

    <div class="card" style="box-shadow: var(--shadow-md); border-radius: var(--radius-lg); overflow: hidden;">
        <!-- Header Bar -->
        <div style="background: linear-gradient(135deg, #1e293b, #0f172a); color: #ffffff; padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div>
                <div style="font-size: 0.82rem; color: #94a3b8; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                    LEMBAR PENILAIAN SISWA &bull; {{ strtoupper($selectedClass) }}
                </div>
                <h2 style="font-size: 1.35rem; margin: 4px 0 0; color: #ffffff; font-weight: 800;">
                    Form Input Nilai & Catatan Evaluasi (Bulan {{ $selectedMonth ?? $selectedWeek }})
                </h2>
            </div>
            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <a href="{{ route('admin.grades.export', ['class_name' => $selectedClass, 'month' => $selectedMonth]) }}" 
                   class="btn btn-sm btn-success" 
                   style="background: #10b981; border-color: #10b981; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px;">
                    📥 Unduh Excel
                </a>
                <button type="submit" class="btn btn-warning" style="font-weight: 800; padding: 10px 22px; font-size: 0.95rem; box-shadow: 0 4px 14px rgba(245, 158, 11, 0.4);">
                    💾 Simpan Semua Nilai & Feedback
                </button>
            </div>
        </div>

        <div style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 10px 1.5rem; font-size: 0.82rem; color: #475569; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
            <div>
                💡 <strong>Kalkulasi Otomatis:</strong> Nilai <strong>Attendance</strong>, <strong>Total Exam</strong>, dan <strong>Final Score</strong> akan terhitung otomatis seketika saat Anda mengetik skor. Anda juga dapat mengubah nilainya secara manual.
            </div>
            <div style="font-family: monospace; font-size: 0.78rem;">
                Rumus: Total Exam = (Flu+Gra+Pro+Voc)/4 &bull; Final = (Att+Exam)/2
            </div>
        </div>

        <div class="table-responsive" style="margin: 0;">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.86rem; text-align: center;">
                <thead>
                    <tr style="background: #f1f5f9; border-bottom: 1px solid #cbd5e1; font-weight: 800; color: #0f172a;">
                        <th rowspan="2" style="border: 1px solid #cbd5e1; padding: 10px 6px; width: 45px;">NO</th>
                        <th rowspan="2" style="border: 1px solid #cbd5e1; padding: 10px 12px; text-align: left; min-width: 180px;">NAMA SISWA</th>
                        <th rowspan="2" style="border: 1px solid #cbd5e1; padding: 10px 12px; text-align: left; min-width: 180px;">FEEDBACK</th>
                        <th colspan="5" style="border: 1px solid #cbd5e1; padding: 8px; background: #e0f2fe; color: #0369a1;">MEETING / KEHADIRAN</th>
                        <th colspan="5" style="border: 1px solid #cbd5e1; padding: 8px; background: #fef3c7; color: #b45309;">EXAMINATION (UJIAN)</th>
                        <th rowspan="2" style="border: 1px solid #cbd5e1; padding: 10px 8px; width: 100px; background: #e0e7ff; color: #3730a3;">FINAL SCORE</th>
                    </tr>
                    <tr style="background: #ffffff; border-bottom: 2px solid #94a3b8; font-weight: 700; font-size: 0.8rem; color: #334155;">
                        <!-- Meeting 1-4 & Attendance -->
                        <th style="border: 1px solid #cbd5e1; padding: 6px 4px; width: 55px; background: #f0f9ff;">M1</th>
                        <th style="border: 1px solid #cbd5e1; padding: 6px 4px; width: 55px; background: #f0f9ff;">M2</th>
                        <th style="border: 1px solid #cbd5e1; padding: 6px 4px; width: 55px; background: #f0f9ff;">M3</th>
                        <th style="border: 1px solid #cbd5e1; padding: 6px 4px; width: 55px; background: #f0f9ff;">M4</th>
                        <th style="border: 1px solid #cbd5e1; padding: 6px 6px; width: 85px; background: #e0f2fe; color: #0284c7;">ATTENDANCE</th>

                        <!-- Examination (Fluency, Grammar, Pronunciation, Vocabulary, Total) -->
                        <th style="border: 1px solid #cbd5e1; padding: 6px 4px; width: 65px; background: #fffbeb;">Fluency</th>
                        <th style="border: 1px solid #cbd5e1; padding: 6px 4px; width: 65px; background: #fffbeb;">Grammar</th>
                        <th style="border: 1px solid #cbd5e1; padding: 6px 4px; width: 70px; background: #fffbeb;">Pronunc.</th>
                        <th style="border: 1px solid #cbd5e1; padding: 6px 4px; width: 65px; background: #fffbeb;">Vocab.</th>
                        <th style="border: 1px solid #cbd5e1; padding: 6px 6px; width: 85px; background: #fef3c7; color: #b45309;">TOTAL EXAM</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $idx => $student)
                        @php
                            $g = $gradesList[$student->id] ?? null;
                            $sid = $student->id;
                        @endphp
                        <tr style="border-bottom: 1px solid #e2e8f0; background: {{ $loop->iteration % 2 === 0 ? '#fafafa' : '#ffffff' }};" id="row_{{ $sid }}">
                            <!-- No -->
                            <td style="border: 1px solid #cbd5e1; padding: 8px 4px; font-weight: 700; color: #64748b;">
                                {{ $idx + 1 }}
                            </td>

                            <!-- Student Info -->
                            <td style="border: 1px solid #cbd5e1; padding: 8px 12px; text-align: left;">
                                <div style="font-weight: 800; color: #0f172a; font-size: 0.92rem;">
                                    {{ $student->name }}
                                </div>
                                <div style="display: flex; gap: 6px; margin-top: 2px;">
                                    <span class="badge" style="background: #f1f5f9; color: #475569; font-size: 0.72rem; padding: 2px 6px;">
                                        🏢 {{ $student->division ?: 'Divisi Umum' }}
                                    </span>
                                </div>
                            </td>

                            <!-- Feedback -->
                            <td style="border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left;">
                                <textarea name="grades[{{ $sid }}][feedback]" class="form-control" rows="2" style="font-size: 0.82rem; resize: vertical; min-height: 50px;" placeholder="Tuliskan catatan kemajuan siswa di Bulan {{ $selectedMonth ?? $selectedWeek }}...">{{ $g ? $g->feedback : '' }}</textarea>
                            </td>

                            <!-- Meetings 1-4 -->
                            <td style="border: 1px solid #cbd5e1; padding: 4px;">
                                <input type="number" step="any" min="0" max="100" name="grades[{{ $sid }}][meeting_1]" id="m1_{{ $sid }}" class="form-control text-center score-input" value="{{ $g ? $g->meeting_1 : 0 }}" oninput="recomputeRow({{ $sid }})" style="padding: 6px 2px; font-weight: 700; font-size: 0.88rem;">
                            </td>
                            <td style="border: 1px solid #cbd5e1; padding: 4px;">
                                <input type="number" step="any" min="0" max="100" name="grades[{{ $sid }}][meeting_2]" id="m2_{{ $sid }}" class="form-control text-center score-input" value="{{ $g ? $g->meeting_2 : 0 }}" oninput="recomputeRow({{ $sid }})" style="padding: 6px 2px; font-weight: 700; font-size: 0.88rem;">
                            </td>
                            <td style="border: 1px solid #cbd5e1; padding: 4px;">
                                <input type="number" step="any" min="0" max="100" name="grades[{{ $sid }}][meeting_3]" id="m3_{{ $sid }}" class="form-control text-center score-input" value="{{ $g ? $g->meeting_3 : 0 }}" oninput="recomputeRow({{ $sid }})" style="padding: 6px 2px; font-weight: 700; font-size: 0.88rem;">
                            </td>
                            <td style="border: 1px solid #cbd5e1; padding: 4px;">
                                <input type="number" step="any" min="0" max="100" name="grades[{{ $sid }}][meeting_4]" id="m4_{{ $sid }}" class="form-control text-center score-input" value="{{ $g ? $g->meeting_4 : 0 }}" oninput="recomputeRow({{ $sid }})" style="padding: 6px 2px; font-weight: 700; font-size: 0.88rem;">
                            </td>

                            <!-- Attendance Score (Calculated) -->
                            <td style="border: 1px solid #cbd5e1; padding: 4px; background: #f0f9ff;">
                                <input type="number" step="any" min="0" max="100" name="grades[{{ $sid }}][attendance_score]" id="att_{{ $sid }}" class="form-control text-center score-input" value="{{ $g ? $g->attendance_score : 0 }}" oninput="recomputeRow({{ $sid }}, true)" style="padding: 6px 2px; font-weight: 800; font-size: 0.9rem; color: #0369a1; background: #ffffff;">
                            </td>

                            <!-- Examination: Fluency, Grammar, Pronunciation, Vocabulary -->
                            <td style="border: 1px solid #cbd5e1; padding: 4px; background: #fffdf5;">
                                <input type="number" step="any" min="0" max="100" name="grades[{{ $sid }}][fluency]" id="flu_{{ $sid }}" class="form-control text-center score-input" value="{{ $g ? $g->fluency : 0 }}" oninput="recomputeRow({{ $sid }})" style="padding: 6px 2px; font-weight: 700; font-size: 0.88rem;">
                            </td>
                            <td style="border: 1px solid #cbd5e1; padding: 4px; background: #fffdf5;">
                                <input type="number" step="any" min="0" max="100" name="grades[{{ $sid }}][grammar]" id="gra_{{ $sid }}" class="form-control text-center score-input" value="{{ $g ? $g->grammar : 0 }}" oninput="recomputeRow({{ $sid }})" style="padding: 6px 2px; font-weight: 700; font-size: 0.88rem;">
                            </td>
                            <td style="border: 1px solid #cbd5e1; padding: 4px; background: #fffdf5;">
                                <input type="number" step="any" min="0" max="100" name="grades[{{ $sid }}][pronunciation]" id="pro_{{ $sid }}" class="form-control text-center score-input" value="{{ $g ? $g->pronunciation : 0 }}" oninput="recomputeRow({{ $sid }})" style="padding: 6px 2px; font-weight: 700; font-size: 0.88rem;">
                            </td>
                            <td style="border: 1px solid #cbd5e1; padding: 4px; background: #fffdf5;">
                                <input type="number" step="any" min="0" max="100" name="grades[{{ $sid }}][vocabulary]" id="voc_{{ $sid }}" class="form-control text-center score-input" value="{{ $g ? $g->vocabulary : 0 }}" oninput="recomputeRow({{ $sid }})" style="padding: 6px 2px; font-weight: 700; font-size: 0.88rem;">
                            </td>

                            <!-- Total Exam (Calculated) -->
                            <td style="border: 1px solid #cbd5e1; padding: 4px; background: #fefce8;">
                                <input type="number" step="any" min="0" max="100" name="grades[{{ $sid }}][total_exam]" id="tot_{{ $sid }}" class="form-control text-center score-input" value="{{ $g ? $g->total_exam : 0 }}" oninput="recomputeRow({{ $sid }}, true)" style="padding: 6px 2px; font-weight: 800; font-size: 0.9rem; color: #b45309; background: #ffffff;">
                            </td>

                            <!-- Final Score (Calculated) -->
                            <td style="border: 1px solid #cbd5e1; padding: 4px; background: #eef2ff;">
                                <input type="number" step="any" min="0" max="100" name="grades[{{ $sid }}][final_score]" id="fin_{{ $sid }}" class="form-control text-center score-input" value="{{ $g ? $g->final_score : 0 }}" style="padding: 6px 2px; font-weight: 900; font-size: 1rem; color: #4338ca; background: #ffffff;">
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="14" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                                Belum ada siswa yang terdaftar di kelas <strong>{{ $selectedClass }}</strong>. Siswa dapat mendaftar dengan memilih kelas ini saat registrasi, atau Anda dapat menambahkan siswa di menu <strong>Kelola Siswa</strong>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($students->isNotEmpty())
            <div style="padding: 1.25rem 1.5rem; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                    <span style="font-size: 0.85rem; color: var(--text-muted);">
                        Total {{ $students->count() }} siswa ditampilkan untuk kelas {{ $selectedClass }} (Bulan {{ $selectedMonth ?? $selectedWeek }}).
                    </span>
                    <a href="{{ route('admin.grades.export', ['class_name' => $selectedClass, 'month' => $selectedMonth]) }}" 
                       class="btn btn-sm btn-outline-success" 
                       style="border-color: #10b981; color: #047857; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                        📥 Download Excel Bulan {{ $selectedMonth }}
                    </a>
                </div>
                <button type="submit" class="btn btn-primary" style="font-weight: 800; padding: 12px 28px; font-size: 1rem; box-shadow: 0 4px 12px rgba(37,99,235,0.3);">
                    💾 Simpan Semua Nilai & Feedback Sekarang
                </button>
            </div>
        @endif
    </div>
</form>

@push('scripts')
<script>
function recomputeRow(sid, manualOverride = false) {
    const m1 = parseFloat(document.getElementById('m1_' + sid).value) || 0;
    const m2 = parseFloat(document.getElementById('m2_' + sid).value) || 0;
    const m3 = parseFloat(document.getElementById('m3_' + sid).value) || 0;
    const m4 = parseFloat(document.getElementById('m4_' + sid).value) || 0;

    let att = parseFloat(document.getElementById('att_' + sid).value) || 0;
    if (!manualOverride) {
        att = (m1 + m2 + m3 + m4) / 4;
        document.getElementById('att_' + sid).value = att.toFixed(1);
    }

    const flu = parseFloat(document.getElementById('flu_' + sid).value) || 0;
    const gra = parseFloat(document.getElementById('gra_' + sid).value) || 0;
    const pro = parseFloat(document.getElementById('pro_' + sid).value) || 0;
    const voc = parseFloat(document.getElementById('voc_' + sid).value) || 0;

    let tot = parseFloat(document.getElementById('tot_' + sid).value) || 0;
    if (!manualOverride) {
        tot = (flu + gra + pro + voc) / 4;
        document.getElementById('tot_' + sid).value = tot.toFixed(1);
    }

    const fin = (att + tot) / 2;
    document.getElementById('fin_' + sid).value = fin.toFixed(1);
}

function toggleDownloadDropdown() {
    const menu = document.getElementById('downloadDropdownMenu');
    if (menu) {
        menu.style.display = menu.style.display === 'none' || menu.style.display === '' ? 'block' : 'none';
    }
}

document.addEventListener('click', function(e) {
    const container = document.getElementById('downloadDropdownContainer');
    const menu = document.getElementById('downloadDropdownMenu');
    if (container && menu && !container.contains(e.target)) {
        menu.style.display = 'none';
    }
});
</script>
<style>
.download-menu-item:hover {
    background-color: #f1f5f9;
}
</style>
@endpush
@endsection

