@extends('layouts.app')

@section('title', 'Tinjau Slide: ' . $material->title . ' - Musashi Admin')

@section('content')
<div style="margin-bottom: 1.5rem;">
    <div style="display: flex; gap: 10px; margin-bottom: 0.75rem; flex-wrap: wrap;">
        <a href="{{ route('admin.materials.index') }}" class="btn btn-secondary btn-sm">
            &larr; Kembali ke Daftar Materi
        </a>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary btn-sm">
            Dashboard
        </a>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                <span class="badge" style="background: {{ $level->subject->badge_color }}15; color: {{ $level->subject->badge_color }}; font-weight: 700;">
                    {{ $level->subject->name }}
                </span>
                <span class="badge badge-primary" style="font-weight: 700;">🗓️ {{ $level->name }}</span>
                @if($material->class_name)
                    <span class="badge" style="background: #eff6ff; color: #1e40af; font-weight: 700;">🎓 {{ $material->class_name }}</span>
                @endif
                <span class="badge badge-neutral">{{ $material->file_type_label }}</span>
            </div>
            <h1 style="font-size: 1.85rem; margin: 0; color: #0f172a;">Pratinjau Materi: {{ $material->title }}</h1>
        </div>

        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            @if($material->file_path)
                <a href="{{ route('admin.materials.download', $material) }}" class="btn btn-secondary" title="Unduh file dokumen/slide asli">
                    ⬇ Unduh File ({{ strtoupper($material->file_type ?? 'FILE') }})
                </a>
            @endif
            <a href="{{ route('admin.materials.edit', $material) }}" class="btn btn-primary">
                ✏️ Edit Materi Ini
            </a>
        </div>
    </div>
</div>

@if($material->description)
    <div class="card" style="margin-bottom: 1.5rem; border-left: 4px solid var(--color-primary);">
        <div class="card-body" style="padding: 1rem 1.5rem; font-size: 0.95rem; color: #334155;">
            <strong>Ringkasan Modul:</strong> {{ $material->description }}
        </div>
    </div>
@endif

<!-- Document / Slide Interactive Viewer Area -->
<div class="card" id="materialViewerCard" style="box-shadow: var(--shadow-lg); overflow: hidden; margin-bottom: 2rem; border-radius: var(--radius-lg); border: 1px solid var(--border-color);">
    <!-- Viewer Top Toolbar -->
    <div style="background: #1e1b4b; color: #ffffff; padding: 12px 18px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 1.3rem;">{{ $material->file_icon }}</span>
            <div>
                <span style="font-weight: 700; font-size: 0.95rem; color: #f8fafc; display: block;">
                    {{ $material->file_name ?? $material->title }}
                </span>
                <span style="font-size: 0.75rem; color: #94a3b8;">
                    Berkas Asli: {{ strtoupper($material->file_type ?? 'DOKUMEN') }} &bull; {{ $material->isPpt() ? 'Presentasi Slide' : ($material->isPdf() ? 'Dokumen PDF' : 'Materi Belajar') }}
                </span>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            @if($material->isPpt())
                <!-- Presentation View Modes -->
                <div style="display: inline-flex; background: rgba(255,255,255,0.1); border-radius: 6px; padding: 3px; border: 1px solid rgba(255,255,255,0.2);">
                    @if(!empty($presentationData['has_slides']))
                        <button type="button" id="btnModeSlides" onclick="switchViewMode('slides')" class="btn btn-sm" style="background: #4f46e5; color: #fff; border: none; font-size: 0.8rem; font-weight: 600; padding: 4px 10px;">
                            🖥️ Slide Interaktif
                        </button>
                    @endif
                    <button type="button" id="btnModeOffice" onclick="switchViewMode('office')" class="btn btn-sm" style="background: {{ empty($presentationData['has_slides']) ? '#4f46e5' : 'transparent' }}; color: {{ empty($presentationData['has_slides']) ? '#fff' : '#cbd5e1' }}; border: none; font-size: 0.8rem; font-weight: 600; padding: 4px 10px;">
                        🖥️ Office Web
                    </button>
                    <button type="button" id="btnModeGoogle" onclick="switchViewMode('google')" class="btn btn-sm" style="background: transparent; color: #cbd5e1; border: none; font-size: 0.8rem; font-weight: 600; padding: 4px 10px;">
                        🌐 Google Docs
                    </button>
                    @if(!empty($presentationData['pdf_url']))
                        <button type="button" id="btnModePdf" onclick="switchViewMode('pdf')" class="btn btn-sm" style="background: transparent; color: #cbd5e1; border: none; font-size: 0.8rem; font-weight: 600; padding: 4px 10px;">
                            📄 PDF
                        </button>
                    @endif
                </div>
            @endif

            @if($material->file_path)
                <a href="{{ route('admin.materials.preview', $material) }}" target="_blank" class="btn btn-secondary btn-sm" style="background: rgba(255,255,255,0.15); color: #fff; border: 1px solid rgba(255,255,255,0.25);" title="Buka pratinjau dokumen / slide di tab baru browser">
                    ↗ Buka di Tab Baru
                </a>
                @if($material->isPpt())
                    <form action="{{ route('admin.materials.reconvert', $material) }}" method="POST" style="display: inline-block;">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm" style="background: rgba(255,255,255,0.15); color: #fff; border: 1px solid rgba(255,255,255,0.25);" title="Konversi ulang slide presentasi jika slide belum tampil optimal">
                            🔄 Konversi Ulang
                        </button>
                    </form>
                @endif
                <a href="{{ route('admin.materials.download', $material) }}" class="btn btn-warning btn-sm" style="font-weight: 600;">
                    ⬇ Unduh
                </a>
            @endif

            <button type="button" class="btn btn-secondary btn-sm" onclick="toggleViewerFullscreen()" style="background: rgba(255,255,255,0.15); color: #fff; border: 1px solid rgba(255,255,255,0.25);" title="Layar Penuh">
                ⛶ Layar Penuh
            </button>
        </div>
    </div>

    <!-- Viewer Body -->
    <div id="viewerContainer" style="background: #0f172a; position: relative;">
        @if($material->isPdf())
            <!-- Native In-Browser PDF Viewer for PDF files (Direct PDF Document without slide mode) -->
            <div class="pdf-viewer-wrap" style="height: 750px; width: 100%; background: #525659;">
                <object data="{{ route('admin.materials.preview', $material) }}#toolbar=1&navpanes=1" type="application/pdf" width="100%" height="100%">
                    <iframe src="{{ route('admin.materials.preview', $material) }}#toolbar=1&navpanes=1" width="100%" height="100%" style="border: none; display: block;" title="{{ $material->title }}">
                        <div style="padding: 3rem; text-align: center; color: #ffffff;">
                            <p style="margin-bottom: 1rem; font-size: 1.1rem; font-weight: 600;">Dokumen PDF Materi Siap Dipelajari</p>
                            <p style="margin-bottom: 1.5rem; color: #cbd5e1; font-size: 0.9rem;">Pratinjau langsung di dalam browser sedang dimuat atau browser Anda membutuhkan pembuka PDF eksternal.</p>
                            <div style="display: flex; justify-content: center; gap: 10px; flex-wrap: wrap;">
                                <a href="{{ route('admin.materials.preview', $material) }}" target="_blank" class="btn btn-primary btn-lg" style="font-weight: 700;">
                                    ↗ Buka PDF di Tab Baru
                                </a>
                                <a href="{{ route('admin.materials.download', $material) }}" class="btn btn-warning btn-lg" style="font-weight: 700;">
                                    ⬇ Unduh File PDF Materi
                                </a>
                            </div>
                        </div>
                    </iframe>
                </object>
            </div>

        @elseif($material->isPpt() && !empty($presentationData['has_slides']))
            <!-- Slide Presentation View (Extracted from PPT file) -->
            <div id="slideModeView" style="display: block;">
                <!-- Sub-toolbar: Slide navigation controls -->
                <div style="background: #1e293b; padding: 10px 18px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span class="badge badge-warning" style="font-weight: 700; font-size: 0.82rem;">📊 Slide Presentasi PPT</span>
                        <span style="font-size: 0.9rem; color: #cbd5e1;">
                            Slide <strong id="currentSlideNum" style="color: #60a5fa; font-size: 1.1rem;">1</strong> dari <strong>{{ $presentationData['total_slides'] }}</strong>
                        </span>
                    </div>

                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="prevSlide()" id="btnPrevSlide" style="background: #334155; color: #fff; border: none; font-weight: 600;" title="Slide Sebelumnya (Tombol Panah Kiri)">
                            ◀ Sebelumnya
                        </button>
                        <select id="slideSelectDropdown" onchange="goToSlide(parseInt(this.value))" style="background: #334155; color: #fff; border: 1px solid #475569; padding: 4px 10px; border-radius: 6px; font-size: 0.85rem; font-weight: 600;">
                            @for($i = 1; $i <= $presentationData['total_slides']; $i++)
                                <option value="{{ $i }}">Slide {{ $i }}</option>
                            @endfor
                        </select>
                        <button type="button" class="btn btn-primary btn-sm" onclick="nextSlide()" id="btnNextSlide" style="font-weight: 600;" title="Slide Berikutnya (Tombol Panah Kanan / Spasi)">
                            Berikutnya ▶
                        </button>
                    </div>
                </div>

                <!-- Slide Stage (Displays the REAL high-res slide image) -->
                <div id="slideStage" style="min-height: 520px; max-height: 75vh; padding: 1.5rem; background: radial-gradient(circle at center, #1e293b 0%, #0f172a 100%); display: flex; align-items: center; justify-content: center; position: relative;">
                    @foreach($presentationData['slides'] as $index => $slideUrl)
                        <div class="real-slide-item" id="slide-item-{{ $loop->iteration }}" style="display: {{ $loop->first ? 'flex' : 'none' }}; align-items: center; justify-content: center; width: 100%; height: 100%; animation: fadeIn 0.2s ease-in-out;">
                            <img src="{{ $slideUrl }}" alt="Slide {{ $loop->iteration }}" id="slide-img-{{ $loop->iteration }}" style="max-width: 100%; max-height: 68vh; object-fit: contain; box-shadow: 0 10px 30px rgba(0,0,0,0.6); border-radius: 6px; background: #ffffff;">
                        </div>
                    @endforeach
                </div>

                <!-- Bottom Slide Thumbnails Strip -->
                <div style="background: #090d16; padding: 12px 14px; border-top: 1px solid rgba(255,255,255,0.1); overflow-x: auto; white-space: nowrap; display: flex; gap: 12px;" id="slideThumbTrack">
                    @foreach($presentationData['slides'] as $index => $slideUrl)
                        <div class="slide-thumb-card" id="thumb-{{ $loop->iteration }}" onclick="goToSlide({{ $loop->iteration }})" style="display: inline-flex; flex-direction: column; align-items: center; gap: 4px; padding: 6px; border-radius: 6px; cursor: pointer; border: 2px solid {{ $loop->first ? '#4f46e5' : 'rgba(255,255,255,0.1)' }}; background: {{ $loop->first ? 'rgba(79,70,229,0.25)' : 'rgba(255,255,255,0.04)' }}; transition: all 0.2s; min-width: 110px;" title="Slide {{ $loop->iteration }}">
                            <img src="{{ $slideUrl }}" alt="Thumb {{ $loop->iteration }}" style="width: 100px; height: 58px; object-fit: cover; border-radius: 4px; display: block; background: #ffffff;">
                            <span style="font-size: 0.72rem; color: #cbd5e1; font-weight: 700;">Slide {{ $loop->iteration }}</span>
                        </div>
                    @endforeach
                </div>

                <!-- Footer guidance -->
                <div style="padding: 10px 18px; background: #1e293b; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; border-top: 1px solid rgba(255,255,255,0.08);">
                    <div style="font-size: 0.82rem; color: #94a3b8;">
                        💡 <strong>Navigasi:</strong> Tekan tombol keyboard <strong>&larr; Panah Kiri</strong> dan <strong>Panah Kanan &rarr;</strong> atau klik thumbnail untuk berpindah slide.
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <a href="{{ route('admin.materials.download', $material) }}" class="btn btn-warning btn-sm" style="font-weight: 600;">
                            ⬇ Unduh Berkas PPT Asli ({{ strtoupper($material->file_type ?? 'PPT') }})
                        </a>
                    </div>
                </div>
            </div>

            <!-- PDF MODE VIEW (If user toggles to PDF or wants to view as native PDF) -->
            @if(!empty($presentationData['pdf_url']))
                <div id="pdfModeView" style="display: none; height: 750px; width: 100%; background: #525659;">
                    <iframe
                        src="{{ $presentationData['pdf_url'] }}#toolbar=1&navpanes=1"
                        style="width: 100%; height: 100%; border: none; display: block;"
                        title="{{ $material->title }}"
                    ></iframe>
                </div>
            @endif

        @elseif($material->file_path && $material->isPpt())
            <!-- Embedded Presentation Web Mode (Microsoft Office Online Viewer & Google Docs Viewer) -->
            <div id="embedModeView" class="embed-viewer-wrap" style="display: {{ empty($presentationData['has_slides']) ? 'block' : 'none' }}; width: 100%; background: #0f172a;">
                <div style="background: #1e293b; padding: 10px 18px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span class="badge badge-warning" style="font-weight: 700; font-size: 0.82rem;">📊 Presentasi PowerPoint (PPTX)</span>
                        <span style="font-size: 0.82rem; color: #cbd5e1;" id="currentEngineText">
                            Penampil: <strong>Microsoft Office Viewer</strong>
                        </span>
                    </div>
                    <div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">
                        <button type="button" class="btn btn-sm" id="btnEngineOfficeSub" onclick="switchViewMode('office')" style="background: #4f46e5; color: #fff; border: none; font-size: 0.78rem; font-weight: 600; padding: 4px 10px; border-radius: 4px;">
                            🖥️ Office Viewer
                        </button>
                        <button type="button" class="btn btn-sm" id="btnEngineGoogleSub" onclick="switchViewMode('google')" style="background: #334155; color: #cbd5e1; border: none; font-size: 0.78rem; font-weight: 600; padding: 4px 10px; border-radius: 4px;">
                            🌐 Google Viewer
                        </button>
                        <a href="{{ $material->public_viewer_url }}" target="_blank" class="btn btn-secondary btn-sm" style="font-size: 0.78rem; background: rgba(255,255,255,0.15); color: #fff; border: 1px solid rgba(255,255,255,0.25); padding: 4px 10px;">
                            ↗ Buka Berkas
                        </a>
                        <a href="{{ route('admin.materials.download', $material) }}" class="btn btn-warning btn-sm" style="font-size: 0.78rem; font-weight: 700; padding: 4px 10px;">
                            ⬇ Unduh PPT
                        </a>
                    </div>
                </div>

                <div style="position: relative; width: 100%; height: 750px; background: #0f172a;">
                    <iframe
                        id="pptEmbedIframe"
                        src="{{ $material->office_embed_url }}"
                        style="width: 100%; height: 100%; border: none; display: block; background: #ffffff;"
                        allowfullscreen="true"
                        mozallowfullscreen="true"
                        webkitallowfullscreen="true"
                        title="{{ $material->title }}"
                    ></iframe>
                </div>

                <div style="padding: 10px 18px; background: #1e293b; font-size: 0.82rem; color: #94a3b8; border-top: 1px solid rgba(255,255,255,0.08); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                    <div>
                        💡 <strong>Tips:</strong> Anda dapat membolak-balik slide PPT langsung di atas, memperbesar slide, atau klik tombol <strong>⛶ Layar Penuh</strong> untuk presentasi di proyektor / kelas. Jika tampilan lambat dimuat, silakan klik tombol <strong>🌐 Google Viewer</strong> di atas.
                    </div>
                </div>
            </div>

            @if(!empty($presentationData['pdf_url']))
                <!-- Converted PDF for PPT file -->
                <div id="pdfModeView" class="pdf-viewer-wrap" style="display: none; height: 750px; width: 100%; background: #525659;">
                    <object data="{{ $presentationData['pdf_url'] }}#toolbar=1&navpanes=1" type="application/pdf" width="100%" height="100%">
                        <iframe src="{{ $presentationData['pdf_url'] }}#toolbar=1&navpanes=1" width="100%" height="100%" style="border: none; display: block;" title="{{ $material->title }}">
                            <div style="padding: 3rem; text-align: center; color: #ffffff;">
                                <p style="margin-bottom: 1rem; font-size: 1.1rem; font-weight: 600;">Presentasi PPT Materi Siap Dipelajari</p>
                                <a href="{{ route('admin.materials.preview', $material) }}" target="_blank" class="btn btn-primary btn-lg" style="font-weight: 700;">
                                    ↗ Buka Dokumen Presentasi di Tab Baru
                                </a>
                            </div>
                        </iframe>
                    </object>
                </div>
            @endif

        @elseif($material->file_path && $material->isImage())
            <!-- Original Image Viewer -->
            <div style="padding: 2rem; text-align: center; background: #0f172a; min-height: 450px; display: flex; align-items: center; justify-content: center;">
                <img src="{{ route('admin.materials.preview', $material) }}" alt="{{ $material->title }}" style="max-width: 100%; max-height: 720px; object-fit: contain; border-radius: var(--radius-md); box-shadow: 0 10px 25px rgba(0,0,0,0.5);">
            </div>

        @elseif($material->slide_url)
            <!-- External Slide URL (Canva / Google Slides) -->
            <div style="background: #0f172a; padding: 1.5rem; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1);">
                <a href="{{ $material->slide_url }}" target="_blank" class="btn btn-primary" style="font-size: 1rem; padding: 10px 20px;">
                    🌐 Buka Slide Presentasi di Halaman Baru &rarr;
                </a>
            </div>
            <div style="position: relative; width: 100%; height: 650px;">
                <iframe
                    src="{{ $material->embed_slide_url ?? $material->slide_url }}"
                    style="width: 100%; height: 100%; border: none;"
                    allowfullscreen="allowfullscreen"
                    title="{{ $material->title }}"
                ></iframe>
            </div>

        @else
            <div style="padding: 3rem; text-align: center; color: #94a3b8; background: #0f172a;">
                <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">📄</div>
                <p>Tidak ada lampiran file yang terhubung pada materi ini.</p>
            </div>
        @endif
    </div>
</div>

@if($material->content)
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-header">
            <h2 style="font-size: 1.15rem; margin: 0;">📖 Teks / Penjelasan Materi Tambahan</h2>
        </div>
        <div class="card-body" style="font-size: 1rem; line-height: 1.8; color: #334155;">
            {!! nl2br(e($material->content)) !!}
        </div>
    </div>
@endif

<!-- Section: Ulasan & Komentar Siswa per Pertemuan (Moderasi & Balasan Guru) -->
<div class="card" id="meetingCommentsAdminSection" style="margin-bottom: 2rem; box-shadow: var(--shadow-sm); border-radius: var(--radius-lg); border: 1px solid #e2e8f0; overflow: hidden;">
    <div class="card-header" style="background: #ffffff; padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; flex-wrap: wrap; gap: 12px;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                <span style="font-size: 1.25rem;">💬</span>
                <h3 style="font-size: 1.2rem; margin: 0; font-weight: 800; color: #0f172a;">
                    Ulasan & Komentar Siswa: {{ $level->name }}
                </h3>
            </div>
            <p style="font-size: 0.84rem; color: var(--text-muted); margin: 0;">
                Lihat refleksi pembelajaran siswa dan berikan tanggapan / balasan guru untuk kelas yang dipilih.
            </p>
        </div>

        <!-- Filter Kelas untuk Guru -->
        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <form action="{{ route('admin.materials.show', $material) }}" method="GET" style="display: flex; align-items: center; gap: 6px; margin: 0;">
                <label for="class_group_filter" style="font-size: 0.8rem; font-weight: 700; color: #475569; margin: 0;">Filter Kelas:</label>
                <select name="class_group" id="class_group_filter" class="form-select form-select-sm" onchange="this.form.submit()" style="font-size: 0.84rem; font-weight: 700; padding: 4px 10px; border-radius: 6px; border: 1px solid #cbd5e1;">
                    <option value="all" {{ $selectedClassGroup === 'all' ? 'selected' : '' }}>Semua Kelas</option>
                    @foreach($allClassGroups as $grp)
                        <option value="{{ $grp }}" {{ $selectedClassGroup === $grp ? 'selected' : '' }}>
                            Kelas {{ $grp }}
                        </option>
                    @endforeach
                </select>
            </form>
            <span class="badge" style="background: #eff6ff; color: #1e40af; font-weight: 700; font-size: 0.85rem; padding: 6px 12px;">
                {{ $meetingComments->count() }} Ulasan
            </span>
        </div>
    </div>

    <div class="card-body" style="padding: 1.5rem;">
        <!-- Form Balas / Kirim Pesan Pembuka Guru -->
        <div style="margin-bottom: 2rem; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 1.25rem;">
            <form action="{{ route('admin.materials.comments.store', $material) }}" method="POST">
                @csrf
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 8px;">
                    <div style="font-weight: 800; font-size: 0.92rem; color: #065f46; display: flex; align-items: center; gap: 6px;">
                        <span>👨‍🏫 Tambah Catatan / Pengumuman Diskusi Guru:</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <span style="font-size: 0.78rem; font-weight: 600; color: #047857;">Target Kelas:</span>
                        <select name="target_class_group" class="form-select form-select-sm" style="font-size: 0.8rem; font-weight: 700; padding: 3px 8px; border-radius: 6px;">
                            @foreach($allClassGroups as $grp)
                                <option value="{{ $grp }}" {{ ($selectedClassGroup === $grp || $defaultClassGroup === $grp) ? 'selected' : '' }}>
                                    Kelas {{ $grp }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <textarea name="content" rows="2" class="form-control" placeholder="Tulis catatan, arahan, atau feedback guru untuk siswa pada pertemuan ini..." required style="width: 100%; border-radius: 8px; border: 1px solid #a7f3d0; padding: 8px 12px; font-size: 0.9rem;"></textarea>

                <div style="display: flex; justify-content: flex-end; margin-top: 8px;">
                    <button type="submit" class="btn btn-success btn-sm" style="font-weight: 700; padding: 6px 16px;">
                        💬 Kirim Catatan Guru
                    </button>
                </div>
            </form>
        </div>

        <!-- Daftar Komentar Siswa -->
        <div style="display: flex; flex-direction: column; gap: 1.25rem;">
            @forelse($meetingComments as $comment)
                @php
                    $isTeacherComment = $comment->user->isAdmin();
                    $initial = strtoupper(substr($comment->user->name ?? 'U', 0, 1));
                @endphp
                <div style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem; background: #ffffff;" id="admin-comment-{{ $comment->id }}">
                    <!-- Header -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 10px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 38px; height: 38px; border-radius: 50%; background: {{ $isTeacherComment ? 'linear-gradient(135deg, #10b981, #059669)' : 'linear-gradient(135deg, #3b82f6, #1d4ed8)' }}; color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.95rem; flex-shrink: 0;">
                                {{ $initial }}
                            </div>
                            <div>
                                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                    <span style="font-weight: 800; font-size: 0.92rem; color: #0f172a;">
                                        {{ $comment->user->name }}
                                    </span>
                                    @if($isTeacherComment)
                                        <span class="badge" style="background: #d1fae5; color: #065f46; font-weight: 800; font-size: 0.72rem;">
                                            👨‍🏫 Guru Pengajar
                                        </span>
                                    @else
                                        <span class="badge" style="background: #eff6ff; color: #1e40af; font-size: 0.72rem; font-weight: 700;">
                                            🎓 Kelas {{ $comment->class_name }} (Grup {{ $comment->class_group }})
                                        </span>
                                    @endif
                                </div>
                                <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 2px;">
                                    {{ $comment->created_at->diffForHumans() }} &bull; {{ $comment->created_at->format('d M Y, H:i') }}
                                </div>
                            </div>
                        </div>

                        <div style="display: flex; align-items: center; gap: 8px;">
                            @if($comment->rating)
                                <div style="color: #f59e0b; font-size: 0.88rem; letter-spacing: 1px;" title="{{ $comment->rating }} Bintang">
                                    @for($r = 1; $r <= 5; $r++)
                                        {{ $r <= $comment->rating ? '★' : '☆' }}
                                    @endfor
                                </div>
                            @endif

                            <form action="{{ route('admin.materials.comments.destroy', $comment) }}" method="POST" onsubmit="return confirm('Hapus komentar ini?');" style="margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm" style="background: none; border: none; color: #ef4444; font-size: 0.82rem; padding: 2px 6px; cursor: pointer;" title="Moderasi: Hapus komentar ini">
                                    🗑️
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Content -->
                    <div style="font-size: 0.92rem; color: #334155; line-height: 1.6; white-space: pre-line; padding-left: 48px;">
                        {{ $comment->content }}
                    </div>

                    <!-- Button: Balas sebagai Guru -->
                    <div style="padding-left: 48px; margin-top: 8px;">
                        <button type="button" onclick="toggleAdminReplyForm({{ $comment->id }})" style="background: none; border: none; color: #059669; font-weight: 700; font-size: 0.82rem; cursor: pointer; padding: 0; display: inline-flex; align-items: center; gap: 4px;">
                            💬 Balas Sebagai Guru
                        </button>
                    </div>

                    <!-- Inline Reply Form for Teacher -->
                    <div id="admin-reply-form-{{ $comment->id }}" style="display: none; margin-top: 12px; margin-left: 48px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 12px;">
                        <form action="{{ route('admin.materials.comments.store', $material) }}" method="POST">
                            @csrf
                            <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                            <div style="font-weight: 700; font-size: 0.82rem; color: #065f46; margin-bottom: 6px;">
                                Balas ulasan {{ $comment->user->name }} (Siswa Kelas {{ $comment->class_name }}):
                            </div>
                            <textarea name="content" rows="2" class="form-control" placeholder="Tulis balasan penjelasan atau apresiasi untuk siswa ini..." required style="width: 100%; border-radius: 6px; border: 1px solid #86efac; padding: 8px 12px; font-size: 0.86rem;"></textarea>
                            <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 8px;">
                                <button type="button" onclick="toggleAdminReplyForm({{ $comment->id }})" class="btn btn-secondary btn-sm" style="font-size: 0.78rem;">
                                    Batal
                                </button>
                                <button type="submit" class="btn btn-success btn-sm" style="font-size: 0.78rem; font-weight: 700;">
                                    Kirim Balasan Guru
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Replies -->
                    @if($comment->replies->isNotEmpty())
                        <div style="margin-left: 48px; margin-top: 14px; display: flex; flex-direction: column; gap: 10px;">
                            @foreach($comment->replies as $reply)
                                @php
                                    $isTeacherReply = $reply->user->isAdmin();
                                    $replyInitial = strtoupper(substr($reply->user->name ?? 'U', 0, 1));
                                @endphp
                                <div style="background: {{ $isTeacherReply ? '#f0fdf4' : '#f8fafc' }}; border: 1px solid {{ $isTeacherReply ? '#bbf7d0' : '#e2e8f0' }}; border-left: 3px solid {{ $isTeacherReply ? '#10b981' : '#6366f1' }}; border-radius: 8px; padding: 10px 14px;">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 8px;">
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <div style="width: 28px; height: 28px; border-radius: 50%; background: {{ $isTeacherReply ? '#10b981' : '#6366f1' }}; color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.76rem; flex-shrink: 0;">
                                                {{ $replyInitial }}
                                            </div>
                                            <div>
                                                <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                                    <span style="font-weight: 800; font-size: 0.85rem; color: #0f172a;">
                                                        {{ $reply->user->name }}
                                                    </span>
                                                    @if($isTeacherReply)
                                                        <span class="badge" style="background: #dcfce7; color: #166534; font-weight: 800; font-size: 0.7rem;">
                                                            👨‍🏫 Balasan Guru
                                                        </span>
                                                    @else
                                                        <span class="badge badge-neutral" style="font-size: 0.68rem;">
                                                            Kelas {{ $reply->class_name }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <div style="font-size: 0.72rem; color: #94a3b8;">
                                                    {{ $reply->created_at->diffForHumans() }}
                                                </div>
                                            </div>
                                        </div>

                                        <form action="{{ route('admin.materials.comments.destroy', $reply) }}" method="POST" onsubmit="return confirm('Hapus balasan ini?');" style="margin: 0;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm" style="background: none; border: none; color: #ef4444; font-size: 0.72rem; padding: 0; cursor: pointer;" title="Hapus balasan">
                                                🗑️
                                            </button>
                                        </form>
                                    </div>
                                    <div style="font-size: 0.88rem; color: #334155; line-height: 1.5; margin-top: 6px; padding-left: 36px; white-space: pre-line;">
                                        {{ $reply->content }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <div style="padding: 2.5rem 1.5rem; text-align: center; color: var(--text-muted); background: #f8fafc; border-radius: 12px; border: 1px dashed #cbd5e1;">
                    <div style="font-size: 2.2rem; margin-bottom: 0.5rem;">💬</div>
                    <h4 style="font-weight: 700; color: #334155; margin-bottom: 4px; font-size: 1rem;">
                        Belum ada ulasan siswa untuk pertemuan ini
                        @if($selectedClassGroup !== 'all')
                            pada Kelas {{ $selectedClassGroup }}
                        @endif
                    </h4>
                    <p style="font-size: 0.85rem; margin: 0; color: #64748b;">
                        Ulasan dan pertanyaan yang dikirim siswa saat mempelajari materi akan muncul di sini.
                    </p>
                </div>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleAdminReplyForm(id) {
    const el = document.getElementById('admin-reply-form-' + id);
    if (!el) return;
    el.style.display = (el.style.display === 'none' || el.style.display === '') ? 'block' : 'none';
}

let currentSlide = 1;
const totalSlides = {{ $presentationData['total_slides'] ?? 0 }};

function goToSlide(num) {
    if (num < 1 || num > totalSlides) return;

    // Hide old slide
    const oldSlide = document.getElementById('slide-item-' + currentSlide);
    if (oldSlide) oldSlide.style.display = 'none';

    const oldThumb = document.getElementById('thumb-' + currentSlide);
    if (oldThumb) {
        oldThumb.style.borderColor = 'rgba(255,255,255,0.1)';
        oldThumb.style.background = 'rgba(255,255,255,0.04)';
    }

    currentSlide = num;

    // Show new slide
    const newSlide = document.getElementById('slide-item-' + currentSlide);
    if (newSlide) newSlide.style.display = 'flex';

    const newThumb = document.getElementById('thumb-' + currentSlide);
    if (newThumb) {
        newThumb.style.borderColor = '#4f46e5';
        newThumb.style.background = 'rgba(79,70,229,0.25)';
        newThumb.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
    }

    const indicator = document.getElementById('currentSlideNum');
    if (indicator) indicator.innerText = currentSlide;

    const dropdown = document.getElementById('slideSelectDropdown');
    if (dropdown) dropdown.value = currentSlide;

    const btnPrev = document.getElementById('btnPrevSlide');
    if (btnPrev) btnPrev.disabled = (currentSlide === 1);

    const btnNext = document.getElementById('btnNextSlide');
    if (btnNext) btnNext.disabled = (currentSlide === totalSlides);
}

function nextSlide() {
    if (currentSlide < totalSlides) {
        goToSlide(currentSlide + 1);
    }
}

function prevSlide() {
    if (currentSlide > 1) {
        goToSlide(currentSlide - 1);
    }
}

const officeEmbedUrl = @json($material->office_embed_url);
const googleEmbedUrl = @json($material->google_embed_url);

function switchViewMode(mode) {
    const slideView = document.getElementById('slideModeView');
    const embedView = document.getElementById('embedModeView');
    const pdfView = document.getElementById('pdfModeView');
    const iframe = document.getElementById('pptEmbedIframe');
    const engineText = document.getElementById('currentEngineText');

    const btnSlides = document.getElementById('btnModeSlides');
    const btnOffice = document.getElementById('btnModeOffice');
    const btnGoogle = document.getElementById('btnModeGoogle');
    const btnPdf = document.getElementById('btnModePdf');
    const btnOfficeSub = document.getElementById('btnEngineOfficeSub');
    const btnGoogleSub = document.getElementById('btnEngineGoogleSub');

    [btnSlides, btnOffice, btnGoogle, btnPdf].forEach(btn => {
        if (btn) {
            btn.style.background = 'transparent';
            btn.style.color = '#cbd5e1';
        }
    });

    if (mode === 'slides' && slideView) {
        slideView.style.display = 'block';
        if (embedView) embedView.style.display = 'none';
        if (pdfView) pdfView.style.display = 'none';
        if (btnSlides) { btnSlides.style.background = '#4f46e5'; btnSlides.style.color = '#fff'; }
    } else if (mode === 'office') {
        if (slideView) slideView.style.display = 'none';
        if (pdfView) pdfView.style.display = 'none';
        if (embedView) embedView.style.display = 'block';
        if (iframe && officeEmbedUrl) iframe.src = officeEmbedUrl;
        if (engineText) engineText.innerHTML = 'Penampil: <strong>Microsoft Office Viewer</strong>';
        if (btnOffice) { btnOffice.style.background = '#4f46e5'; btnOffice.style.color = '#fff'; }
        if (btnOfficeSub) { btnOfficeSub.style.background = '#4f46e5'; btnOfficeSub.style.color = '#fff'; }
        if (btnGoogleSub) { btnGoogleSub.style.background = '#334155'; btnGoogleSub.style.color = '#cbd5e1'; }
    } else if (mode === 'google') {
        if (slideView) slideView.style.display = 'none';
        if (pdfView) pdfView.style.display = 'none';
        if (embedView) embedView.style.display = 'block';
        if (iframe && googleEmbedUrl) iframe.src = googleEmbedUrl;
        if (engineText) engineText.innerHTML = 'Penampil: <strong>Google Docs Viewer</strong>';
        if (btnGoogle) { btnGoogle.style.background = '#4f46e5'; btnGoogle.style.color = '#fff'; }
        if (btnGoogleSub) { btnGoogleSub.style.background = '#4f46e5'; btnGoogleSub.style.color = '#fff'; }
        if (btnOfficeSub) { btnOfficeSub.style.background = '#334155'; btnOfficeSub.style.color = '#cbd5e1'; }
    } else if (mode === 'pdf' && pdfView) {
        if (slideView) slideView.style.display = 'none';
        if (embedView) embedView.style.display = 'none';
        pdfView.style.display = 'block';
        if (btnPdf) { btnPdf.style.background = '#4f46e5'; btnPdf.style.color = '#fff'; }
    }
}

document.addEventListener('keydown', function(e) {
    if (totalSlides > 0) {
        if (e.key === 'ArrowRight' || e.key === 'PageDown' || e.key === ' ') {
            if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') {
                e.preventDefault();
                nextSlide();
            }
        } else if (e.key === 'ArrowLeft' || e.key === 'PageUp') {
            if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') {
                e.preventDefault();
                prevSlide();
            }
        }
    }
});

function toggleViewerFullscreen() {
    const el = document.getElementById('materialViewerCard');
    if (!document.fullscreenElement) {
        if (el.requestFullscreen) {
            el.requestFullscreen();
        } else if (el.webkitRequestFullscreen) {
            el.webkitRequestFullscreen();
        }
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        }
    }
}
</script>
<style>
@keyframes fadeIn {
    from { opacity: 0; transform: scale(0.99); }
    to { opacity: 1; transform: scale(1); }
}
.slide-thumb-card:hover {
    border-color: #818cf8 !important;
}
#materialViewerCard:fullscreen {
    border-radius: 0 !important;
    overflow-y: auto !important;
    height: 100vh !important;
    display: flex;
    flex-direction: column;
}
#materialViewerCard:fullscreen #viewerContainer {
    flex: 1;
    display: flex;
    flex-direction: column;
}
#materialViewerCard:fullscreen #slideModeView {
    flex: 1;
    display: flex;
    flex-direction: column;
}
#materialViewerCard:fullscreen #slideStage {
    flex: 1;
    max-height: none !important;
    min-height: calc(100vh - 180px) !important;
}
#materialViewerCard:fullscreen .pdf-viewer-wrap,
#materialViewerCard:fullscreen .embed-viewer-wrap,
#materialViewerCard:fullscreen #pptEmbedIframe {
    height: 100% !important;
    min-height: calc(100vh - 70px) !important;
}
#materialViewerCard:fullscreen .real-slide-item img {
    max-height: calc(100vh - 200px) !important;
}
</style>
@endpush
@endsection
