@extends('layouts.app')

@section('title', 'Presensi & Absensi Siswa - Musashi Learning')

@section('content')
<div style="margin-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div class="badge badge-primary" style="margin-bottom: 6px;">Learning Musashi Presensi Siswa</div>
            <h1 style="font-size: 1.85rem; margin: 0;">Presensi & Kehadiran Belajar</h1>
            <p style="color: var(--text-muted); font-size: 0.92rem; margin-top: 4px;">
                Catat kehadiran harianmu saat login dan pantau rekapitulasi kehadiran belajar di Musashi.
            </p>
        </div>
        <div style="background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 100%); color: #ffffff; border-radius: var(--radius-md); padding: 12px 20px; text-align: right; box-shadow: var(--shadow-sm); border: 1px solid rgba(255,255,255,0.1);">
            <div style="font-size: 0.72rem; color: #94a3b8; font-weight: 700; text-transform: uppercase; display: flex; align-items: center; justify-content: flex-end; gap: 6px;">
                <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #10b981; box-shadow: 0 0 8px #10b981;"></span>
                Waktu Realtime
            </div>
            <div style="font-size: 1.05rem; font-weight: 800; color: #ffffff; margin-top: 2px;">
                <span id="liveRealtimeDate">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
            </div>
            <div style="font-family: monospace; font-size: 1.35rem; font-weight: 800; color: #38bdf8; margin-top: 2px;">
                <span id="liveRealtimeClock">{{ \Carbon\Carbon::now()->format('H:i:s') }}</span> <span style="font-size: 0.8rem; font-weight: 600; color: #94a3b8;">WIB</span>
            </div>
        </div>
    </div>
</div>

<!-- Today's Attendance Widget Card -->
<div class="card" style="box-shadow: var(--shadow-md); margin-bottom: 2.5rem; border: 2px solid {{ $todayAttendance ? '#10b981' : 'var(--color-primary)' }};">
    <div class="card-header" style="background: {{ $todayAttendance ? '#f0fdf4' : '#f8faff' }}; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 44px; height: 44px; border-radius: var(--radius-md); background: {{ $todayAttendance ? '#10b981' : 'var(--color-primary)' }}; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
                {{ $todayAttendance ? '✅' : '📅' }}
            </div>
            <div>
                <h2 style="font-size: 1.25rem; margin: 0; color: #0f172a;">
                    Presensi Hari Ini: {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
                </h2>
                <div style="font-size: 0.84rem; color: var(--text-muted);">
                    @if($todayAttendance)
                        Presensi berhasil tercatat pada <strong>{{ $todayAttendance->formatted_time }}</strong>
                    @else
                        Silakan pilih status kehadiran Anda untuk hari ini di bawah:
                    @endif
                </div>
            </div>
        </div>
        @if($todayAttendance)
            <span class="badge {{ $todayAttendance->status_badge_class }}" style="font-size: 0.95rem; padding: 6px 14px;">
                {{ $todayAttendance->status_icon }} {{ $todayAttendance->status_label }}
            </span>
        @else
            <span class="badge badge-warning" style="font-size: 0.85rem; padding: 6px 12px;">
                ⏳ Belum Melakukan Presensi
            </span>
        @endif
    </div>

    <div class="card-body" style="padding: 1.75rem;">
        @if($todayAttendance)
            <div style="background: #ffffff; border-radius: var(--radius-md); padding: 1.25rem; border: 1px solid #d1fae5; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.25rem;">
                <div>
                    <div style="font-size: 0.85rem; color: #065f46; font-weight: 700; text-transform: uppercase;">Status Terpilih:</div>
                    <div style="font-size: 1.2rem; font-weight: 800; color: #064e3b; margin-top: 2px;">
                        {{ $todayAttendance->status_label }}
                    </div>
                    @if($todayAttendance->notes)
                        <div style="margin-top: 6px; font-size: 0.9rem; color: #334155; background: #f8fafc; padding: 6px 12px; border-radius: var(--radius-sm); border: 1px dashed #cbd5e1;">
                            <strong>Keterangan Izin:</strong> {{ $todayAttendance->notes }}
                        </div>
                    @endif
                </div>
                <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('editAttendanceForm').style.display = document.getElementById('editAttendanceForm').style.display === 'none' ? 'block' : 'none'">
                    ✏ Ubah Presensi Hari Ini
                </button>
            </div>
        @endif

        <div id="editAttendanceForm" style="{{ $todayAttendance ? 'display: none;' : '' }}">
            <p style="font-size: 0.92rem; color: #475569; margin-bottom: 1.25rem;">
                Silakan pilih salah satu opsi kehadiran berikut:
            </p>

            <form action="{{ route('attendance.submit') }}" method="POST" id="attendanceForm">
                @csrf

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                    <!-- Option 1: Hadir -->
                    <label class="attendance-card-label" for="opt_hadir" style="border: 2px solid {{ old('status', $todayAttendance?->status) === 'hadir' || !$todayAttendance ? '#10b981' : '#e2e8f0' }}; border-radius: var(--radius-md); padding: 1.25rem; cursor: pointer; transition: var(--transition); background: #ffffff; display: block; position: relative;" id="card_hadir">
                        <div style="display: flex; align-items: flex-start; gap: 12px;">
                            <input type="radio" name="status" id="opt_hadir" value="hadir" {{ old('status', $todayAttendance?->status ?? 'hadir') === 'hadir' ? 'checked' : '' }} onchange="handleAttendanceOptionChange('hadir')" style="margin-top: 4px; accent-color: #10b981; transform: scale(1.2);">
                            <div>
                                <div style="font-weight: 800; font-size: 1.05rem; color: #065f46; display: flex; align-items: center; gap: 6px;">
                                    <span>✅</span> Hadir
                                </div>
                                <div style="font-size: 0.82rem; color: var(--text-muted); margin-top: 4px;">
                                    Saya hadir dan siap mengikuti kegiatan belajar hari ini.
                                </div>
                            </div>
                        </div>
                    </label>

                    <!-- Option 2: Izin dengan Keterangan -->
                    <label class="attendance-card-label" for="opt_izin_ket" style="border: 2px solid {{ old('status', $todayAttendance?->status) === 'izin_keterangan' ? 'var(--color-primary)' : '#e2e8f0' }}; border-radius: var(--radius-md); padding: 1.25rem; cursor: pointer; transition: var(--transition); background: #ffffff; display: block; position: relative;" id="card_izin_ket">
                        <div style="display: flex; align-items: flex-start; gap: 12px;">
                            <input type="radio" name="status" id="opt_izin_ket" value="izin_keterangan" {{ old('status', $todayAttendance?->status) === 'izin_keterangan' ? 'checked' : '' }} onchange="handleAttendanceOptionChange('izin_keterangan')" style="margin-top: 4px; accent-color: var(--color-primary); transform: scale(1.2);">
                            <div>
                                <div style="font-weight: 800; font-size: 1.05rem; color: var(--color-primary); display: flex; align-items: center; gap: 6px;">
                                    <span>📝</span> Izin dengan Keterangan
                                </div>
                                <div style="font-size: 0.82rem; color: var(--text-muted); margin-top: 4px;">
                                    Izin sakit, keperluan keluarga, atau urusan penting lainnya.
                                </div>
                            </div>
                        </div>
                    </label>

                    <!-- Option 3: Izin tanpa Keterangan -->
                    <label class="attendance-card-label" for="opt_izin_tanpa" style="border: 2px solid {{ old('status', $todayAttendance?->status) === 'izin_tanpa_keterangan' ? '#ef4444' : '#e2e8f0' }}; border-radius: var(--radius-md); padding: 1.25rem; cursor: pointer; transition: var(--transition); background: #ffffff; display: block; position: relative;" id="card_izin_tanpa">
                        <div style="display: flex; align-items: flex-start; gap: 12px;">
                            <input type="radio" name="status" id="opt_izin_tanpa" value="izin_tanpa_keterangan" {{ old('status', $todayAttendance?->status) === 'izin_tanpa_keterangan' ? 'checked' : '' }} onchange="handleAttendanceOptionChange('izin_tanpa_keterangan')" style="margin-top: 4px; accent-color: #ef4444; transform: scale(1.2);">
                            <div>
                                <div style="font-weight: 800; font-size: 1.05rem; color: #b91c1c; display: flex; align-items: center; gap: 6px;">
                                    <span>⚠️</span> Izin tanpa Keterangan
                                </div>
                                <div style="font-size: 0.82rem; color: var(--text-muted); margin-top: 4px;">
                                    Izin tanpa melampirkan alasan atau berhalangan hadir.
                                </div>
                            </div>
                        </div>
                    </label>
                </div>

                <!-- Notes Input (Shown when 'izin_keterangan' is selected) -->
                <div id="notesContainer" style="margin-bottom: 1.5rem; display: {{ old('status', $todayAttendance?->status) === 'izin_keterangan' ? 'block' : 'none' }}; background: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1.25rem;">
                    <label class="form-label" for="notes" style="font-weight: 700; color: #1e1b4b;">
                        Alasan / Keterangan Izin *
                    </label>
                    <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Contoh: Mengalami demam / sedang rawat inap / izin menghadiri kegiatan keluarga...">{{ old('notes', $todayAttendance?->notes) }}</textarea>
                    <span class="form-text" style="font-size: 0.8rem; color: var(--text-muted);">
                        Mohon tuliskan alasan izin dengan jelas agar pengajar/guru dapat memverifikasi.
                    </span>
                </div>

                <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                    <button type="submit" class="btn btn-primary btn-lg" style="padding: 12px 28px; font-weight: 700;">
                        {{ $todayAttendance ? 'Simpan Perubahan Presensi' : 'Kirim Presensi Sekarang' }}
                    </button>
                    @if($todayAttendance)
                        <button type="button" class="btn btn-secondary" onclick="document.getElementById('editAttendanceForm').style.display='none'">
                            Batal
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Attendance Statistics Summary -->
<!-- Attendance Statistics Summary -->
<div class="grid grid-cols-4" style="margin-bottom: 2.5rem; gap: 14px;">
    <div class="card" style="border-left: 4px solid #10b981; box-shadow: var(--shadow-sm);">
        <div class="card-body" style="padding: 1.25rem;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 0.78rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Jumlah Kehadiran</div>
                    <div style="font-size: 1.9rem; font-weight: 800; color: #10b981; margin-top: 2px;">
                        {{ $summary['total_hadir'] }} <span style="font-size: 0.85rem; font-weight: 600; color: #059669;">Hari</span>
                    </div>
                </div>
                <div style="font-size: 2rem; opacity: 0.9;">✅</div>
            </div>
            <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 4px;">Dari {{ $summary['total_days'] }} hari tercatat</div>
        </div>
    </div>

    <div class="card" style="border-left: 4px solid #3b82f6; box-shadow: var(--shadow-sm);">
        <div class="card-body" style="padding: 1.25rem;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 0.78rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Persentase Kehadiran</div>
                    <div style="font-size: 1.9rem; font-weight: 800; color: #2563eb; margin-top: 2px;">
                        {{ $summary['percentage'] }}%
                    </div>
                </div>
                <div style="font-size: 2rem; opacity: 0.9;">📈</div>
            </div>
            <div style="font-size: 0.78rem; color: #2563eb; font-weight: 600; margin-top: 4px;">Tingkat kedisiplinan belajar</div>
        </div>
    </div>

    <div class="card" style="border-left: 4px solid #f59e0b; box-shadow: var(--shadow-sm);">
        <div class="card-body" style="padding: 1.25rem;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 0.78rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Total Izin</div>
                    <div style="font-size: 1.9rem; font-weight: 800; color: #d97706; margin-top: 2px;">
                        {{ $summary['total_izin_keterangan'] + $summary['total_izin_tanpa_keterangan'] }} <span style="font-size: 0.85rem; font-weight: 600; color: #b45309;">Hari</span>
                    </div>
                </div>
                <div style="font-size: 2rem; opacity: 0.9;">📝</div>
            </div>
            <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 4px;">{{ $summary['total_izin_keterangan'] }} izin ket. &bull; {{ $summary['total_izin_tanpa_keterangan'] }} tanpa ket.</div>
        </div>
    </div>

    <div class="card" style="border-left: 4px solid #8b5cf6; box-shadow: var(--shadow-sm);">
        <div class="card-body" style="padding: 1.25rem;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 0.78rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Waktu Presensi Hari Ini</div>
                    <div style="font-size: 1.3rem; font-weight: 800; color: #4338ca; margin-top: 4px; font-family: monospace;">
                        @if($todayAttendance)
                            {{ $todayAttendance->formatted_time }} <span style="font-size: 0.75rem; font-family: sans-serif; font-weight: 600;">WIB</span>
                        @else
                            <span style="color: #d97706; font-size: 1.05rem; font-family: sans-serif;">Belum Presensi</span>
                        @endif
                    </div>
                </div>
                <div style="font-size: 2rem; opacity: 0.9;">⏱️</div>
            </div>
            <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 4px;">
                @if($todayAttendance)
                    Status: <strong style="color: #065f46;">{{ $todayAttendance->status_label }}</strong>
                @else
                    Silakan isi presensi di atas
                @endif
            </div>
        </div>
    </div>
</div>

<!-- History Table -->
<div class="card">
    <div class="card-header">
        <div>
            <h3 style="font-size: 1.2rem; margin: 0;">Riwayat Presensi Kehadiran</h3>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin: 2px 0 0;">Daftar lengkap catatan kehadiran Anda di platform.</p>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 60px;">No</th>
                    <th>Hari & Tanggal</th>
                    <th>Waktu Presensi</th>
                    <th>Status Kehadiran</th>
                    <th>Keterangan / Alasan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $idx => $att)
                    <tr>
                        <td>{{ $attendances->firstItem() + $idx }}</td>
                        <td>
                            <strong style="color: #0f172a;">{{ $att->formatted_date }}</strong>
                            @if($att->date->isToday())
                                <span class="badge badge-primary" style="font-size: 0.7rem; margin-left: 4px;">Hari Ini</span>
                            @endif
                        </td>
                        <td>
                            <span style="font-family: monospace; font-size: 0.9rem; color: #334155;">{{ $att->formatted_time }}</span>
                        </td>
                        <td>
                            <span class="badge {{ $att->status_badge_class }}">
                                {{ $att->status_icon }} {{ $att->status_label }}
                            </span>
                        </td>
                        <td>
                            @if($att->notes)
                                <span style="font-size: 0.88rem; color: #334155;">{{ $att->notes }}</span>
                            @else
                                <span style="color: var(--text-muted); font-size: 0.85rem; font-style: italic;">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                            <div style="font-size: 3rem; margin-bottom: 0.5rem;">📅</div>
                            <strong>Belum Ada Riwayat Presensi</strong>
                            <p style="font-size: 0.85rem; margin-top: 4px;">Presensi harian yang Anda kirim akan tercatat secara otomatis di sini.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($attendances->hasPages())
        <div style="padding: 1.25rem;">
            {{ $attendances->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
function handleAttendanceOptionChange(val) {
    const notesContainer = document.getElementById('notesContainer');
    const notesInput = document.getElementById('notes');

    const cardHadir = document.getElementById('card_hadir');
    const cardIzinKet = document.getElementById('card_izin_ket');
    const cardIzinTanpa = document.getElementById('card_izin_tanpa');

    // Reset borders
    cardHadir.style.borderColor = '#e2e8f0';
    cardIzinKet.style.borderColor = '#e2e8f0';
    cardIzinTanpa.style.borderColor = '#e2e8f0';

    if (val === 'hadir') {
        notesContainer.style.display = 'none';
        notesInput.removeAttribute('required');
        cardHadir.style.borderColor = '#10b981';
    } else if (val === 'izin_keterangan') {
        notesContainer.style.display = 'block';
        notesInput.setAttribute('required', 'required');
        notesInput.focus();
        cardIzinKet.style.borderColor = 'var(--color-primary)';
    } else if (val === 'izin_tanpa_keterangan') {
        notesContainer.style.display = 'none';
        notesInput.removeAttribute('required');
        cardIzinTanpa.style.borderColor = '#ef4444';
    }
}

function updateRealtimeClock() {
    const now = new Date();
    const optionsTime = { timeZone: 'Asia/Jakarta', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
    const optionsDate = { timeZone: 'Asia/Jakarta', weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    
    const timeEl = document.getElementById('liveRealtimeClock');
    if (timeEl) {
        timeEl.innerText = new Intl.DateTimeFormat('id-ID', optionsTime).format(now).replace(/\./g, ':');
    }
    const dateEl = document.getElementById('liveRealtimeDate');
    if (dateEl) {
        dateEl.innerText = new Intl.DateTimeFormat('id-ID', optionsDate).format(now);
    }
}
setInterval(updateRealtimeClock, 1000);
</script>
@endpush
@endsection
