@extends('layouts.app')

@section('title', 'Kelola Presensi Siswa Realtime - Admin Musashi')

@section('content')
<div style="margin-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div class="badge badge-primary" style="margin-bottom: 6px;">
                {{ $isTeacher ? 'Learning Musashi Guru: ' . ($teacherSubject->name ?? 'Mata Pelajaran') : 'Learning Musashi Pengajar & Admin' }}
            </div>
            <h1 style="font-size: 1.85rem; margin: 0; font-weight: 800;">
                Kelola Presensi Siswa Realtime {{ $selectedClass ? '• ' . $selectedClass : '' }}
            </h1>
            <p style="color: var(--text-muted); font-size: 0.92rem; margin-top: 4px;">
                Pantau kehadiran siswa secara realtime tiap kelas, periksa keterangan izin, dan perbarui status absensi harian.
            </p>
        </div>
        <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
            <div style="background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 100%); color: #ffffff; border-radius: var(--radius-md); padding: 8px 16px; text-align: right; box-shadow: var(--shadow-sm); border: 1px solid rgba(255,255,255,0.1);">
                <div style="font-size: 0.7rem; color: #94a3b8; font-weight: 700; text-transform: uppercase; display: flex; align-items: center; justify-content: flex-end; gap: 6px;">
                    <span style="display: inline-block; width: 7px; height: 7px; border-radius: 50%; background: #10b981; box-shadow: 0 0 6px #10b981;"></span>
                    Waktu Realtime
                </div>
                <div style="font-size: 0.88rem; font-weight: 800; color: #ffffff; margin-top: 1px;">
                    <span id="adminLiveDate">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
                </div>
                <div style="font-family: monospace; font-size: 1.15rem; font-weight: 800; color: #38bdf8; margin-top: 1px;">
                    <span id="adminLiveClock">{{ \Carbon\Carbon::now()->format('H:i:s') }}</span> <span style="font-size: 0.72rem; font-weight: 600; color: #94a3b8;">WIB</span>
                </div>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary btn-sm">
                &larr; Dashboard
            </a>
        </div>
    </div>
</div>

@if($isTeacher)
    <!-- Teacher's Attendance Card -->
    <div class="card" style="margin-bottom: 1.5rem; border-left: 4px solid var(--color-primary); background: #f8fafc;">
        <div class="card-body" style="padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 1.3rem;">🧑‍🏫</span>
                    <strong style="color: #0f172a; font-size: 1rem;">Presensi Mengajar Anda ({{ $teacherSubject->name ?? 'Guru' }}):</strong>
                    @if($teacherDateAttendance)
                        @if($teacherDateAttendance->status === 'hadir')
                            <span class="badge badge-success">✅ Hadir Mengajar</span>
                        @elseif($teacherDateAttendance->status === 'izin_keterangan')
                            <span class="badge badge-primary">📝 Izin dg Keterangan</span>
                        @else
                            <span class="badge badge-danger">⚠️ Izin tanpa Keterangan</span>
                        @endif
                    @else
                        <span class="badge badge-warning">⏳ Belum Presensi</span>
                    @endif
                </div>
                @if($teacherDateAttendance && $teacherDateAttendance->notes)
                    <div style="font-size: 0.82rem; color: #64748b; margin-top: 4px;">
                        Keterangan izin: <em>"{{ $teacherDateAttendance->notes }}"</em> ({{ $teacherDateAttendance->check_in_time ?? '-' }} WIB)
                    </div>
                @endif
            </div>
            <form action="{{ route('attendance.submit') }}" method="POST" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                @csrf
                <select name="status" class="form-control" style="font-size: 0.85rem; padding: 6px 10px; width: auto;" onchange="if(this.value === 'izin_keterangan'){ document.getElementById('teachAttNotes').style.display='inline-block'; document.getElementById('teachAttNotes').focus(); } else { document.getElementById('teachAttNotes').style.display='none'; }">
                    <option value="hadir" {{ (!$teacherDateAttendance || $teacherDateAttendance->status === 'hadir') ? 'selected' : '' }}>✅ Hadir Mengajar</option>
                    <option value="izin_keterangan" {{ ($teacherDateAttendance && $teacherDateAttendance->status === 'izin_keterangan') ? 'selected' : '' }}>📝 Izin dg Keterangan</option>
                    <option value="izin_tanpa_keterangan" {{ ($teacherDateAttendance && $teacherDateAttendance->status === 'izin_tanpa_keterangan') ? 'selected' : '' }}>⚠️ Izin tanpa Ket.</option>
                </select>
                <input type="text" name="notes" id="teachAttNotes" class="form-control" style="font-size: 0.85rem; padding: 6px 10px; width: 220px; display: {{ ($teacherDateAttendance && $teacherDateAttendance->status === 'izin_keterangan') ? 'inline-block' : 'none' }};" placeholder="Tulis alasan izin..." value="{{ $teacherDateAttendance?->notes ?? '' }}">
                <button type="submit" class="btn btn-primary btn-sm">
                    {{ $teacherDateAttendance ? 'Update Presensi Guru' : 'Simpan Presensi Guru' }}
                </button>
            </form>
        </div>
    </div>
@endif

<!-- Date & Class Filter Bar -->
<div class="card" style="margin-bottom: 2rem; box-shadow: var(--shadow-sm);">
    <div class="card-body" style="padding: 1.25rem 1.5rem;">
        <form action="{{ route('admin.attendance.index') }}" method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)) auto; gap: 14px; align-items: flex-end;">
            <div>
                <label class="form-label" for="class_name" style="font-size: 0.84rem; font-weight: 700; color: #1e293b; margin-bottom: 4px;">
                    🏫 Filter Pilihan Kelas:
                </label>
                <select name="class_name" id="class_name" class="form-control" onchange="this.form.submit()" style="font-weight: 600;">
                    <option value="all">-- Semua Kelas / Grup --</option>
                    @if(isset($classes))
                        @foreach($classes as $c)
                            <option value="{{ $c->name }}" {{ $selectedClass === $c->name ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>

            <div>
                <label class="form-label" for="date" style="font-size: 0.84rem; font-weight: 700; color: #1e293b; margin-bottom: 4px;">
                    📅 Tanggal Presensi:
                </label>
                <input type="date" name="date" id="date" class="form-control" value="{{ $selectedDate }}" onchange="this.form.submit()">
            </div>

            <div>
                <label class="form-label" for="search" style="font-size: 0.84rem; font-weight: 700; color: #1e293b; margin-bottom: 4px;">
                    🔍 Cari Siswa / Divisi:
                </label>
                <input type="text" name="search" id="search" class="form-control" value="{{ $search }}" placeholder="Nama, email, atau divisi...">
            </div>

            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary" style="padding: 10px 18px; font-weight: 700;">
                    Terapkan Filter
                </button>
                @if($search || $selectedClass || $selectedDate !== now()->toDateString())
                    <a href="{{ route('admin.attendance.index') }}" class="btn btn-secondary" style="padding: 10px 14px;">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Statistics Cards for the Selected Date & Class -->
<div class="grid grid-cols-4" style="margin-bottom: 2rem;">
    <div class="card" style="border-left: 4px solid #3b82f6;">
        <div class="card-body" style="padding: 1.25rem;">
            <div style="font-size: 0.78rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Total Siswa ({{ $selectedClass ?: 'Semua' }})</div>
            <div style="font-size: 2rem; font-weight: 800; color: #0f172a; margin-top: 4px;">
                {{ $stats['total_students'] }} <span style="font-size: 0.9rem; font-weight: 500; color: var(--text-muted);">Siswa</span>
            </div>
            <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 2px;">Terdaftar di filter aktif</div>
        </div>
    </div>

    <div class="card" style="border-left: 4px solid #10b981;">
        <div class="card-body" style="padding: 1.25rem;">
            <div style="font-size: 0.78rem; font-weight: 700; color: #065f46; text-transform: uppercase;">Hadir Realtime</div>
            <div style="font-size: 2rem; font-weight: 800; color: #10b981; margin-top: 4px;">
                {{ $stats['hadir'] }} <span style="font-size: 0.9rem; font-weight: 500; color: var(--text-muted);">Siswa</span>
            </div>
            <div style="font-size: 0.78rem; color: #059669; margin-top: 2px;">
                {{ $stats['total_students'] > 0 ? round(($stats['hadir'] / $stats['total_students']) * 100) : 0 }}% tingkat kehadiran
            </div>
        </div>
    </div>

    <div class="card" style="border-left: 4px solid var(--color-primary);">
        <div class="card-body" style="padding: 1.25rem;">
            <div style="font-size: 0.78rem; font-weight: 700; color: #4338ca; text-transform: uppercase;">Izin dg Keterangan</div>
            <div style="font-size: 2rem; font-weight: 800; color: var(--color-primary); margin-top: 4px;">
                {{ $stats['izin_keterangan'] }} <span style="font-size: 0.9rem; font-weight: 500; color: var(--text-muted);">Siswa</span>
            </div>
            <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 2px;">Melampirkan alasan izin</div>
        </div>
    </div>

    <div class="card" style="border-left: 4px solid #ef4444;">
        <div class="card-body" style="padding: 1.25rem;">
            <div style="font-size: 0.78rem; font-weight: 700; color: #b91c1c; text-transform: uppercase;">Izin tanpa Keterangan / Belum</div>
            <div style="font-size: 2rem; font-weight: 800; color: #ef4444; margin-top: 4px;">
                {{ $stats['izin_tanpa_keterangan'] + $stats['belum_absen'] }} <span style="font-size: 0.9rem; font-weight: 500; color: var(--text-muted);">Siswa</span>
            </div>
            <div style="font-size: 0.78rem; color: #dc2626; margin-top: 2px;">
                {{ $stats['izin_tanpa_keterangan'] }} Tanpa Ket. &bull; {{ $stats['belum_absen'] }} Belum Absen
            </div>
        </div>
    </div>
</div>

<!-- Student Attendance Table separated by class -->
<div class="card" style="box-shadow: var(--shadow-md);">
    <div class="card-header" style="background: #ffffff; padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div>
            <h3 style="font-size: 1.25rem; margin: 0; font-weight: 800; color: #0f172a;">
                Daftar Presensi Kelas: {{ $selectedClass ?: 'Semua Kelas' }}
            </h3>
            <span style="font-size: 0.84rem; color: var(--text-muted);">
                Tanggal: <strong>{{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('l, d F Y') }}</strong> &bull; Total {{ $students->count() }} siswa
            </span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table" style="vertical-align: middle;">
            <thead>
                <tr>
                    <th style="width: 45px; text-align: center;">No</th>
                    <th>Nama Siswa</th>
                    <th>Divisi</th>
                    <th>Kelas</th>
                    <th>Status Presensi</th>
                    <th>Jam Absen</th>
                    <th>Alasan / Keterangan</th>
                    <th style="text-align: right; width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $idx => $student)
                    @php
                        $att = $attendances->get($student->id);
                    @endphp
                    <tr>
                        <td style="text-align: center; font-weight: 600; color: #64748b;">{{ $idx + 1 }}</td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 36px; height: 36px; border-radius: var(--radius-full); background: #e0e7ff; color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.95rem;">
                                    {{ strtoupper(substr($student->name, 0, 1)) }}
                                </div>
                                <div>
                                    <strong style="color: #0f172a; font-size: 0.95rem;">{{ $student->name }}</strong>
                                    <div style="font-size: 0.78rem; color: var(--text-muted);">{{ $student->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge" style="background: #f1f5f9; color: #334155; font-size: 0.8rem; font-weight: 600;">
                                🏢 {{ $student->division ?: 'Umum' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-primary" style="font-size: 0.8rem; font-weight: 700;">
                                🎓 {{ $student->class_name ?: 'Belum Ada' }}
                            </span>
                        </td>
                        <td>
                            @if($att)
                                <span class="badge {{ $att->status_badge_class }}" style="font-size: 0.84rem;">
                                    {{ $att->status_icon }} {{ $att->status_label }}
                                </span>
                            @else
                                <span class="badge badge-neutral" style="color: #94a3b8; border: 1px dashed #cbd5e1; font-size: 0.82rem;">
                                    ⏳ Belum Absen
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($att)
                                <span style="font-family: monospace; font-size: 0.88rem; font-weight: 700; color: #0f172a;">
                                    {{ $att->formatted_time }} WIB
                                </span>
                            @else
                                <span style="color: var(--text-muted); font-size: 0.85rem;">—</span>
                            @endif
                        </td>
                        <td>
                            @if($att && $att->notes)
                                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: var(--radius-sm); padding: 4px 8px; font-size: 0.82rem; color: #334155; max-width: 250px;">
                                    <em>"{{ $att->notes }}"</em>
                                </div>
                            @else
                                <span style="color: var(--text-muted); font-size: 0.85rem;">—</span>
                            @endif
                        </td>
                        <td style="text-align: right;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="openAdminAttendanceModal({{ $student->id }}, '{{ addslashes($student->name) }}', '{{ $att ? $att->status : 'hadir' }}', '{{ $att ? addslashes($att->notes ?? '') : '' }}')">
                                ✏ {{ $att ? 'Ubah' : 'Input' }}
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                            Tidak ada siswa yang ditemukan pada kelas atau tanggal ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Admin Modal to Mark/Edit Student Attendance -->
<div id="adminAttendanceModal" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.5); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #ffffff; border-radius: var(--radius-lg); max-width: 480px; width: 100%; box-shadow: var(--shadow-xl); overflow: hidden; animation: fadeIn 0.2s ease;">
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 1.2rem; margin: 0; color: #0f172a; font-weight: 700;">Kelola Presensi Siswa</h3>
            <button type="button" onclick="closeAdminAttendanceModal()" style="background:none; border:none; font-size: 1.4rem; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>

        <form action="{{ route('admin.attendance.store') }}" method="POST">
            @csrf
            <div style="padding: 1.5rem;">
                <input type="hidden" name="user_id" id="modal_student_id">
                <input type="hidden" name="date" value="{{ $selectedDate }}">

                <div style="margin-bottom: 1rem;">
                    <div style="font-size: 0.8rem; color: #64748b; font-weight: 700; text-transform: uppercase;">Nama Siswa:</div>
                    <div style="font-size: 1.15rem; font-weight: 800; color: #0f172a;" id="modal_student_name"></div>
                    <div style="font-size: 0.82rem; color: var(--text-muted); margin-top: 2px;">Tanggal: {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('d F Y') }}</div>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label" for="modal_status" style="font-weight: 700;">Status Presensi *</label>
                    <select name="status" id="modal_status" class="form-control" required onchange="toggleModalNotes(this.value)">
                        <option value="hadir">✅ Hadir</option>
                        <option value="izin_keterangan">📝 Izin dengan Keterangan</option>
                        <option value="izin_tanpa_keterangan">⚠️ Izin tanpa Keterangan</option>
                    </select>
                </div>

                <div class="form-group" id="modal_notes_group" style="margin-bottom: 1rem; display: none;">
                    <label class="form-label" for="modal_notes" style="font-weight: 700;">Keterangan / Alasan Izin</label>
                    <textarea name="notes" id="modal_notes" class="form-control" rows="3" placeholder="Tuliskan alasan izin siswa..."></textarea>
                </div>
            </div>

            <div style="padding: 1rem 1.5rem; background: #f8fafc; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary" onclick="closeAdminAttendanceModal()">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Presensi</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openAdminAttendanceModal(studentId, studentName, currentStatus, currentNotes) {
    document.getElementById('modal_student_id').value = studentId;
    document.getElementById('modal_student_name').innerText = studentName;
    document.getElementById('modal_status').value = currentStatus || 'hadir';
    document.getElementById('modal_notes').value = currentNotes || '';

    toggleModalNotes(document.getElementById('modal_status').value);

    const modal = document.getElementById('adminAttendanceModal');
    modal.style.display = 'flex';
}

function closeAdminAttendanceModal() {
    document.getElementById('adminAttendanceModal').style.display = 'none';
}

function toggleModalNotes(status) {
    const notesGroup = document.getElementById('modal_notes_group');
    if (status === 'izin_keterangan') {
        notesGroup.style.display = 'block';
    } else {
        notesGroup.style.display = 'none';
    }
}

function updateAdminClock() {
    const clockEl = document.getElementById('adminLiveClock');
    const dateEl = document.getElementById('adminLiveDate');
    const now = new Date();
    if (clockEl) {
        const optionsTime = { timeZone: 'Asia/Jakarta', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
        clockEl.innerText = new Intl.DateTimeFormat('id-ID', optionsTime).format(now).replace(/\./g, ':');
    }
    if (dateEl) {
        const optionsDate = { timeZone: 'Asia/Jakarta', weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        dateEl.innerText = new Intl.DateTimeFormat('id-ID', optionsDate).format(now);
    }
}
setInterval(updateAdminClock, 1000);
</script>
@endpush
@endsection
