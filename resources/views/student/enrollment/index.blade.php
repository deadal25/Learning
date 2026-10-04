@extends('layouts.app')

@section('title', 'Aktivasi & Beli Mata Pelajaran - Musashi Learning')

@section('content')
<div style="margin-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div class="badge badge-warning" style="margin-bottom: 6px;">Pendaftaran Mata Pelajaran</div>
            <h1 style="font-size: 1.85rem; margin: 0;">Aktivasi & Ambil Kelas Belajar</h1>
            <p style="color: var(--text-muted); font-size: 0.92rem; margin-top: 4px;">
                Aktifkan mata pelajaran dengan memasukkan Kode Kelas resmi dari guru pengajar masing-masing.
            </p>
        </div>
        <div>
            <a href="{{ route('student.dashboard') }}" class="btn btn-secondary btn-sm">
                &larr; Ke Dashboard Belajar
            </a>
        </div>
    </div>
</div>

<!-- Redeem Code Card -->
<div class="card" style="box-shadow: var(--shadow-md); margin-bottom: 2.5rem; border: 2px solid var(--color-primary); background: linear-gradient(135deg, #f8faff 0%, #ffffff 100%);">
    <div class="card-body" style="padding: 2rem;">
        <div style="max-width: 680px; margin: 0 auto; text-align: center;">
            <div style="font-size: 3rem; margin-bottom: 0.5rem;">🎟️</div>
            <h2 style="font-size: 1.5rem; color: #0f172a; margin-bottom: 0.5rem;">
                Masukkan Kode Kelas Guru Pengajar
            </h2>
            <p style="font-size: 0.92rem; color: var(--text-muted); line-height: 1.6; margin-bottom: 1.5rem;">
                Setiap guru Musashi mengajar <strong>1 mata pelajaran khusus</strong> dan memiliki <strong>Kode Kelas unik</strong>. Masukkan kode kelas dari guru Anda di bawah ini untuk membuka akses penuh ke materi & latihan soal.
            </p>

            <form action="{{ route('student.enroll.submit') }}" method="POST" style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; margin-bottom: 1.5rem;">
                @csrf
                <input type="text" name="class_code" id="class_code" class="form-control" placeholder="CONTOH: JPN-KENJI atau ENG-SARAH" required style="max-width: 360px; text-transform: uppercase; font-weight: 800; font-size: 1.1rem; letter-spacing: 1.5px; text-align: center; padding: 12px;">
                <button type="submit" class="btn btn-primary" style="padding: 12px 28px; font-weight: 700; font-size: 1rem;">
                    🚀 Aktifkan Kelas Sekarang
                </button>
            </form>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; text-align: left; background: rgba(79, 70, 229, 0.04); border-radius: var(--radius-md); padding: 14px 18px; border: 1px dashed rgba(79, 70, 229, 0.25);">
                <div style="font-size: 0.82rem; color: #334155;">
                    💡 <strong>1 Guru = 1 Mata Pelajaran</strong><br>
                    Guru khusus fokus membimbing 1 bidang keahlian.
                </div>
                <div style="font-size: 0.82rem; color: #334155;">
                    💡 <strong>Fleksibel Ambil Kelas</strong><br>
                    Anda bisa mengambil 1, 2, atau seluruh mata pelajaran sekaligus.
                </div>
                <div style="font-size: 0.82rem; color: #334155;">
                    💡 <strong>Langsung Aktif</strong><br>
                    Materi dan kuis level 1 langsung dapat diakses setelah aktivasi.
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Subjects Catalog Grid -->
<div style="margin-bottom: 2rem;">
    <h2 style="font-size: 1.35rem; margin-bottom: 1.25rem;">Katalog Mata Pelajaran di Musashi Learning</h2>

    <div class="grid grid-cols-3" style="gap: 1.5rem;">
        @foreach($subjects as $subj)
            @php
                $isEnrolled = isset($enrollments[$subj->id]);
                $enrollment = $enrollments[$subj->id] ?? null;
                $teacher = $teachers[$subj->id] ?? null;
            @endphp
            <div class="card" style="display: flex; flex-direction: column; justify-content: space-between; border-top: 4px solid {{ $subj->badge_color }}; {{ $isEnrolled ? 'box-shadow: 0 0 0 2px #10b981;' : '' }}">
                <div>
                    <div class="card-header" style="background: {{ $subj->badge_color }}10; padding: 1.25rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span class="badge" style="background: {{ $subj->badge_color }}; color: #ffffff; font-size: 0.75rem;">
                                {{ $subj->name }}
                            </span>
                            @if($isEnrolled)
                                <span class="badge badge-success" style="font-size: 0.72rem;">
                                    ✓ AKTIF TERDAFTAR
                                </span>
                            @else
                                <span class="badge badge-neutral" style="font-size: 0.72rem; color: #94a3b8;">
                                    🔒 BELUM DIAKTIFKAN
                                </span>
                            @endif
                        </div>

                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 44px; height: 44px; border-radius: var(--radius-md); background: {{ $subj->badge_color }}; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; font-weight: 800;">
                                {{ substr($subj->name, 0, 1) }}
                            </div>
                            <div>
                                <h3 style="font-size: 1.15rem; margin: 0; color: #0f172a;">{{ $subj->name }}</h3>
                                <span style="font-size: 0.8rem; color: var(--text-muted);">
                                    {{ $subj->levels->count() }} Tingkatan Level Belajar
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="card-body" style="padding: 1.25rem;">
                        <p style="font-size: 0.86rem; color: var(--text-muted); line-height: 1.5; margin-bottom: 1.25rem; min-height: 48px;">
                            {{ $subj->description }}
                        </p>

                        <!-- Teacher Info -->
                        <div style="background: #f8fafc; border-radius: var(--radius-md); padding: 12px; margin-bottom: 1.25rem; border: 1px solid #e2e8f0;">
                            <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: #64748b; margin-bottom: 4px;">
                                👨‍🏫 Guru Pengajar Spesialis:
                            </div>
                            @if($teacher)
                                <div style="font-weight: 800; font-size: 0.95rem; color: #0f172a;">
                                    {{ $teacher->name }}
                                </div>
                                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">
                                    Kode Kelas: <code style="background: #e2e8f0; padding: 2px 6px; border-radius: 4px; font-weight: 700; color: var(--color-primary);">{{ $teacher->class_code ?? '-' }}</code>
                                </div>
                            @else
                                <div style="font-size: 0.85rem; color: var(--text-muted); font-style: italic;">
                                    Guru pengajar sedang ditugaskan.
                                </div>
                            @endif
                        </div>

                        @if($isEnrolled)
                            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: var(--radius-md); padding: 10px 12px; font-size: 0.82rem; color: #166534;">
                                ✅ <strong>Status Aktif:</strong> Anda sudah terdaftar di kelas ini melalui kode <strong>{{ $enrollment->class_code }}</strong>.
                            </div>
                        @else
                            <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: var(--radius-md); padding: 10px 12px; font-size: 0.82rem; color: #92400e;">
                                ℹ️ Butuh Kode Kelas dari <strong>{{ $teacher?->name ?? 'Guru' }}</strong> untuk mengaktifkan mata pelajaran ini.
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card-footer" style="padding: 1rem 1.25rem; background: #f8fafc; border-top: 1px solid var(--border-color);">
                    @if($isEnrolled)
                        <a href="{{ route('student.subjects.show', $subj) }}" class="btn btn-primary" style="width: 100%; text-align: center; font-weight: 700;">
                            Buka Modul Belajar &rarr;
                        </a>
                    @else
                        <form action="{{ route('student.enroll.submit') }}" method="POST" style="display: flex; gap: 8px;">
                            @csrf
                            <input type="text" name="class_code" class="form-control form-control-sm" placeholder="Masukkan Kode Kelas" required style="text-transform: uppercase; font-weight: 700;">
                            <button type="submit" class="btn btn-primary btn-sm" style="white-space: nowrap; font-weight: 700;">
                                Aktifkan
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
