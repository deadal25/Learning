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

<!-- Mobile Quick Helper Banner (Optimized for iPhone SE, 16, 16 Pro Max, iPad, and Tablets) -->
<div class="mobile-material-banner" style="background: linear-gradient(135deg, #1e1b4b, #312e81); color: #fff; padding: 12px 16px; border-radius: var(--radius-md); margin-bottom: 1rem; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; box-shadow: 0 4px 12px rgba(30, 27, 75, 0.2);">
    <div style="display: flex; align-items: center; gap: 10px;">
        <span style="font-size: 1.5rem;">📱</span>
        <div>
            <strong style="font-size: 0.92rem; display: block; color: #f8fafc;">Mode Baca Guru di Ponsel & Tablet</strong>
            <span style="font-size: 0.76rem; color: #cbd5e1;">Usap layar (swipe) untuk ganti slide/halaman, atau buka layar penuh di browser</span>
        </div>
    </div>
    <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
        @if($material->isPdf() || !empty($presentationData['pdf_url']))
            <a href="{{ $material->isPdf() ? route('admin.materials.preview', $material) : $presentationData['pdf_url'] }}" target="_blank" class="btn btn-primary btn-sm" style="font-weight: 700; font-size: 0.82rem; background: #3b82f6; border-color: #3b82f6; display: inline-flex; align-items: center; gap: 6px;">
                <span>↗ Buka Semua Halaman (Safari/Tab Baru)</span>
            </a>
        @endif
        @if($material->file_path)
            <a href="{{ route('admin.materials.download', $material) }}" class="btn btn-warning btn-sm" style="font-weight: 700; font-size: 0.82rem;">
                ⬇ Unduh ({{ strtoupper($material->file_type ?? 'FILE') }})
            </a>
        @endif
        <button type="button" class="btn btn-secondary btn-sm" onclick="toggleViewerFullscreen()" style="font-size: 0.82rem; font-weight: 600; background: rgba(255,255,255,0.18); color: #fff; border: 1px solid rgba(255,255,255,0.3);">
            ⛶ Layar Penuh
        </button>
    </div>
</div>

<!-- Document / Slide Interactive Viewer Area -->
<div class="card" id="materialViewerCard" style="box-shadow: var(--shadow-lg); overflow: hidden; margin-bottom: 2rem; border-radius: var(--radius-lg); border: 1px solid var(--border-color); position: relative;">
    <!-- Floating Exit Fullscreen Button (for Mobile iOS Pseudo-Fullscreen) -->
    <button type="button" id="mobileFullscreenExitBtn" onclick="toggleViewerFullscreen()" style="display: none; position: fixed; top: 14px; right: 14px; z-index: 1000000; background: #ef4444; color: #fff; border: none; border-radius: 9999px; padding: 8px 16px; font-weight: 800; font-size: 0.85rem; box-shadow: 0 4px 14px rgba(0,0,0,0.4); cursor: pointer;">
        ✕ Keluar Layar Penuh
    </button>

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
                <a href="{{ $material->isPdf() ? route('admin.materials.preview', $material) : ($presentationData['pdf_url'] ?? route('admin.materials.download', $material)) }}" target="_blank" class="btn btn-secondary btn-sm" style="background: rgba(255,255,255,0.15); color: #fff; border: 1px solid rgba(255,255,255,0.25);" title="Buka pratinjau dokumen / slide di tab baru browser (Mendukung semua halaman di iOS Safari)">
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
            <!-- SMART PDF VIEWER: Canvas-based PDF.js (Supports iPhone SE, 16, Pro Max, iPad, and Tablets without getting stuck on page 1) -->
            <div id="pdfSmartViewerSection" style="width: 100%; background: #0f172a;">
                <!-- PDF Controls Sub-toolbar -->
                <div style="background: #1e293b; padding: 10px 16px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        <span class="badge" style="background: #dc2626; color: #fff; font-weight: 800; font-size: 0.78rem;">📕 Dokumen PDF</span>
                        <span style="font-size: 0.88rem; color: #cbd5e1;">
                            Halaman <strong id="pdfCurrentPageNum" style="color: #60a5fa; font-size: 1rem;">1</strong> dari <strong id="pdfTotalPages">-</strong>
                        </span>
                    </div>

                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <!-- Navigation Buttons (for Single Page mode) -->
                        <div id="pdfSingleNavControls" style="display: none; align-items: center; gap: 6px;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="prevPdfPage()" id="btnPrevPdfPage" style="background: #334155; color: #fff; border: none; font-weight: 700; font-size: 0.8rem;">
                                ◀ Sebelumnya
                            </button>
                            <select id="pdfPageSelect" onchange="goToPdfPage(parseInt(this.value))" style="background: #334155; color: #fff; border: 1px solid #475569; padding: 4px 8px; border-radius: 6px; font-size: 0.82rem; font-weight: 600;">
                                <option value="1">Halaman 1</option>
                            </select>
                            <button type="button" class="btn btn-primary btn-sm" onclick="nextPdfPage()" id="btnNextPdfPage" style="font-weight: 700; font-size: 0.8rem;">
                                Berikutnya ▶
                            </button>
                        </div>

                        <!-- Mode Switch Buttons -->
                        <div style="display: inline-flex; background: rgba(255,255,255,0.1); border-radius: 6px; padding: 2px;">
                            <button type="button" id="btnPdfModeScroll" onclick="setPdfViewMode('scroll')" class="btn btn-sm" style="background: #2563eb; color: #fff; border: none; font-size: 0.78rem; font-weight: 700; padding: 4px 10px;" title="Gulir ke bawah untuk membaca seluruh halaman secara bersambung">
                                📜 Gulir Semua
                            </button>
                            <button type="button" id="btnPdfModeSingle" onclick="setPdfViewMode('single')" class="btn btn-sm" style="background: transparent; color: #cbd5e1; border: none; font-size: 0.78rem; font-weight: 700; padding: 4px 10px;" title="Buka satu per satu halaman dengan tombol atau swipe geser layar">
                                📄 Per Halaman
                            </button>
                        </div>

                        <!-- Zoom Controls -->
                        <div style="display: inline-flex; background: rgba(255,255,255,0.1); border-radius: 6px; padding: 2px;">
                            <button type="button" onclick="zoomPdf(-0.15)" class="btn btn-sm" style="background: transparent; color: #cbd5e1; border: none; font-size: 0.85rem; padding: 3px 8px;" title="Perkecil">🔍 -</button>
                            <button type="button" onclick="zoomPdf(0.15)" class="btn btn-sm" style="background: transparent; color: #cbd5e1; border: none; font-size: 0.85rem; padding: 3px 8px;" title="Perbesar">🔍 +</button>
                            <button type="button" onclick="resetPdfZoom()" class="btn btn-sm" style="background: transparent; color: #cbd5e1; border: none; font-size: 0.76rem; padding: 3px 8px;" title="Pas Lebar">Fit</button>
                        </div>

                        <a href="{{ route('admin.materials.preview', $material) }}" target="_blank" class="btn btn-secondary btn-sm" style="background: rgba(255,255,255,0.15); color: #fff; border: 1px solid rgba(255,255,255,0.25); font-size: 0.8rem; font-weight: 600;" title="Buka langsung di tab baru (Native Apple PDF Scroll pada iPhone/iPad)">
                            ↗ Tab Baru
                        </a>
                    </div>
                </div>

                <!-- PDF Content Stage -->
                <div id="pdfPagesScrollStage" style="position: relative; width: 100%; min-height: 520px; max-height: 80vh; overflow-y: auto; overflow-x: auto; -webkit-overflow-scrolling: touch; padding: 1.25rem 0.75rem; background: radial-gradient(circle at center, #1e293b 0%, #0f172a 100%);">
                    <!-- Loading Indicator -->
                    <div id="pdfLoadingIndicator" style="padding: 4rem 1.5rem; text-align: center; color: #ffffff;">
                        <div style="display: inline-block; width: 42px; height: 42px; border: 4px solid rgba(255,255,255,0.2); border-top-color: #38bdf8; border-radius: 50%; animation: spin 0.8s linear infinite; margin-bottom: 1rem;"></div>
                        <h4 style="font-size: 1.1rem; margin: 0 0 6px 0; font-weight: 700; color: #f8fafc;">Memuat Dokumen PDF...</h4>
                        <p style="font-size: 0.85rem; color: #94a3b8; margin: 0;">Menyiapkan seluruh halaman materi agar dapat digulir di ponsel & tablet.</p>
                    </div>

                    <!-- Canvas Container (All pages rendered here) -->
                    <div id="pdfPagesContainer" style="width: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 14px;"></div>

                    <!-- Native Fallback (Shows if PDF.js fails or device requires direct open) -->
                    <div id="pdfNativeFallback" style="display: none; padding: 2rem 1.5rem; text-align: center; color: #ffffff;">
                        <div style="font-size: 2.2rem; margin-bottom: 0.5rem;">📄</div>
                        <h4 style="font-size: 1.15rem; font-weight: 800; color: #ffffff; margin-bottom: 8px;">Dokumen PDF Siap Dibuka</h4>
                        <p style="max-width: 540px; margin: 0 auto 1.25rem auto; font-size: 0.88rem; color: #cbd5e1; line-height: 1.5;">
                            Untuk pengalaman membaca terbaik di iPhone / iPad / Tablet, Anda dapat membuka dokumen secara langsung di browser atau mengunduh berkasnya.
                        </p>
                        <div style="display: flex; justify-content: center; gap: 10px; flex-wrap: wrap;">
                            <a href="{{ route('admin.materials.preview', $material) }}" target="_blank" class="btn btn-primary btn-lg" style="font-weight: 700;">
                                ↗ Buka PDF di Tab Baru (Layar Penuh)
                            </a>
                            <a href="{{ route('admin.materials.download', $material) }}" class="btn btn-warning btn-lg" style="font-weight: 700;">
                                ⬇ Unduh File PDF
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Footer guidance -->
                <div style="padding: 10px 18px; background: #1e293b; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; border-top: 1px solid rgba(255,255,255,0.08); font-size: 0.82rem; color: #94a3b8;">
                    <div>
                        💡 <strong>Ponsel & Tablet:</strong> Anda dapat menggulir ke bawah untuk membaca seluruh halaman secara bersambung, atau beralih ke <strong>Mode Per Halaman</strong> untuk usap layar (swipe).
                    </div>
                    <a href="{{ route('admin.materials.preview', $material) }}" target="_blank" style="color: #60a5fa; text-decoration: underline; font-weight: 600;">
                        Buka dokumen penuh di Safari &rarr;
                    </a>
                </div>
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

                <!-- Slide Stage (Displays the REAL high-res slide image with Touch Gestures & Mobile Tap Arrows) -->
                <div id="slideStage" style="min-height: 480px; max-height: 75vh; padding: 1.25rem; background: radial-gradient(circle at center, #1e293b 0%, #0f172a 100%); display: flex; align-items: center; justify-content: center; position: relative; user-select: none;">
                    <!-- Floating Mobile Thumb Tap Arrows -->
                    <button type="button" class="mobile-slide-tap-arrow left" onclick="prevSlide()" aria-label="Slide Sebelumnya" title="Slide Sebelumnya">
                        ‹
                    </button>
                    <button type="button" class="mobile-slide-tap-arrow right" onclick="nextSlide()" aria-label="Slide Berikutnya" title="Slide Berikutnya">
                        ›
                    </button>

                    @foreach($presentationData['slides'] as $index => $slideUrl)
                        <div class="real-slide-item" id="slide-item-{{ $loop->iteration }}" style="display: {{ $loop->first ? 'flex' : 'none' }}; align-items: center; justify-content: center; width: 100%; height: 100%; animation: fadeIn 0.2s ease-in-out;">
                            <img src="{{ $slideUrl }}" alt="Slide {{ $loop->iteration }}" id="slide-img-{{ $loop->iteration }}" style="max-width: 100%; max-height: 68vh; object-fit: contain; box-shadow: 0 10px 30px rgba(0,0,0,0.6); border-radius: 6px; background: #ffffff;">
                        </div>
                    @endforeach
                </div>

                <!-- Bottom Slide Thumbnails Strip -->
                <div style="background: #090d16; padding: 12px 14px; border-top: 1px solid rgba(255,255,255,0.1); overflow-x: auto; white-space: nowrap; display: flex; gap: 12px; -webkit-overflow-scrolling: touch;" id="slideThumbTrack">
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
                        💡 <strong>Navigasi:</strong> Usap layar (swipe kiri/kanan), ketuk panah samping, atau gunakan tombol panah keyboard untuk berpindah slide.
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <a href="{{ route('admin.materials.download', $material) }}" class="btn btn-warning btn-sm" style="font-weight: 600;">
                            ⬇ Unduh Berkas PPT Asli ({{ strtoupper($material->file_type ?? 'PPT') }})
                        </a>
                    </div>
                </div>
            </div>

            <!-- PDF MODE VIEW (If user toggles to PDF or wants to view as PDF) -->
            @if(!empty($presentationData['pdf_url']))
                <div id="pdfModeView" style="display: none; width: 100%; background: #0f172a;">
                    <div style="padding: 1rem; text-align: center;">
                        <a href="{{ $presentationData['pdf_url'] }}" target="_blank" class="btn btn-primary" style="font-weight: 700; margin-bottom: 12px;">
                            ↗ Buka Dokumen Presentasi PDF Penuh di Tab Baru
                        </a>
                    </div>
                    <div style="width: 100%; height: 75vh; min-height: 480px;">
                        <iframe
                            src="{{ $presentationData['pdf_url'] }}#toolbar=1&navpanes=1"
                            style="width: 100%; height: 100%; border: none; display: block;"
                            title="{{ $material->title }}"
                        ></iframe>
                    </div>
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
                   <span style="font-size: 1.25rem;">💬</span>
                <h3 style="font-size: 1.2rem; margin: 0; font-weight: 800; color: #0f172a;">
                    Ulasan & Komentar Siswa: {{ $level->name }} {{ $isJapanese ? '(Bahasa Jepang)' : '' }}
                </h3>
            </div>
            <p style="font-size: 0.84rem; color: var(--text-muted); margin: 0;">
                @if($isJapanese)
                    Diskusi ulasan terbuka untuk semua grup siswa (Grup 1 s/d 13). Sensei dapat melihat dan membalas langsung ulasan dari semua grup di sini.
                @else
                    Lihat refleksi pembelajaran siswa dan berikan tanggapan / balasan guru untuk kelas yang dipilih.
                @endif
            </p>
        </div>

        <!-- Filter Kelas untuk Guru -->
        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <form action="{{ route('admin.materials.show', $material) }}" method="GET" style="display: flex; align-items: center; gap: 6px; margin: 0;">
                <label for="class_group_filter" style="font-size: 0.8rem; font-weight: 700; color: #475569; margin: 0;">{{ $isJapanese ? 'Filter Grup:' : 'Filter Kelas:' }}</label>
                <select name="class_group" id="class_group_filter" class="form-select form-select-sm" onchange="this.form.submit()" style="font-size: 0.84rem; font-weight: 700; padding: 4px 10px; border-radius: 6px; border: 1px solid #cbd5e1;">
                    <option value="all" {{ $selectedClassGroup === 'all' ? 'selected' : '' }}>{{ $isJapanese ? 'Semua Grup Siswa' : 'Semua Kelas' }}</option>
                    @foreach($allClassGroups as $grp)
                        @if($grp !== 'Semua Grup')
                            <option value="{{ $grp }}" {{ $selectedClassGroup === $grp ? 'selected' : '' }}>
                                {{ $isJapanese ? $grp : 'Kelas ' . $grp }}
                            </option>
                        @endif
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
                        <span>{{ $isJapanese ? '🌸 Tambah Catatan / Pengumuman Sensei:' : '👨‍🏫 Tambah Catatan / Pengumuman Diskusi Guru:' }}</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 6px;">
                        @if($isJapanese)
                            <span class="badge" style="background: #d1fae5; color: #065f46; font-weight: 800; font-size: 0.78rem;">
                                Target: Seluruh Grup Siswa {{ $level->name }}
                            </span>
                            <input type="hidden" name="target_class_group" value="Semua Grup">
                        @else
                            <span style="font-size: 0.78rem; font-weight: 600; color: #047857;">Target Kelas:</span>
                            <select name="target_class_group" class="form-select form-select-sm" style="font-size: 0.8rem; font-weight: 700; padding: 3px 8px; border-radius: 6px;">
                                @foreach($allClassGroups as $grp)
                                    <option value="{{ $grp }}" {{ ($selectedClassGroup === $grp || $defaultClassGroup === $grp) ? 'selected' : '' }}>
                                        Kelas {{ $grp }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                </div>

                <textarea name="content" rows="2" class="form-control" placeholder="{{ $isJapanese ? 'Tulis catatan, arahan, atau motivasi Sensei untuk siswa pada pertemuan ini...' : 'Tulis catatan, arahan, atau feedback guru untuk siswa pada pertemuan ini...' }}" required style="width: 100%; border-radius: 8px; border: 1px solid #a7f3d0; padding: 8px 12px; font-size: 0.9rem;"></textarea>

                <div style="display: flex; justify-content: flex-end; margin-top: 8px;">
                    <button type="submit" class="btn btn-success btn-sm" style="font-weight: 700; padding: 6px 16px;">
                        💬 {{ $isJapanese ? 'Kirim Catatan Sensei' : 'Kirim Catatan Guru' }}
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
                                            👨‍🏫 {{ $isJapanese ? 'Sensei (Guru Bahasa Jepang)' : 'Guru Pengajar' }}
                                        </span>
                                    @else
                                        <span class="badge" style="background: #eff6ff; color: #1e40af; font-size: 0.72rem; font-weight: 700;">
                                            🎓 {{ $isJapanese ? ($comment->class_name ?: 'Siswa') : 'Kelas ' . $comment->class_name . ' (Grup ' . $comment->class_group . ')' }}
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

                    <!-- Button: Balas sebagai Guru / Sensei -->
                    <div style="padding-left: 48px; margin-top: 8px;">
                        <button type="button" onclick="toggleAdminReplyForm({{ $comment->id }})" style="background: none; border: none; color: #059669; font-weight: 700; font-size: 0.82rem; cursor: pointer; padding: 0; display: inline-flex; align-items: center; gap: 4px;">
                            💬 {{ $isJapanese ? 'Balas Sebagai Sensei' : 'Balas Sebagai Guru' }}
                        </button>
                    </div>

                    <!-- Inline Reply Form for Teacher -->
                    <div id="admin-reply-form-{{ $comment->id }}" style="display: none; margin-top: 12px; margin-left: 48px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 12px;">
                        <form action="{{ route('admin.materials.comments.store', $material) }}" method="POST">
                            @csrf
                            <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                            <div style="font-weight: 700; font-size: 0.82rem; color: #065f46; margin-bottom: 6px;">
                                Balas ulasan {{ $comment->user->name }} ({{ $comment->class_name }}):
                            </div>
                            <textarea name="content" rows="2" class="form-control" placeholder="Tulis balasan penjelasan atau apresiasi untuk siswa ini..." required style="width: 100%; border-radius: 6px; border: 1px solid #86efac; padding: 8px 12px; font-size: 0.86rem;"></textarea>
                            <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 8px;">
                                <button type="button" onclick="toggleAdminReplyForm({{ $comment->id }})" class="btn btn-secondary btn-sm" style="font-size: 0.78rem;">
                                    Batal
                                </button>
                                <button type="submit" class="btn btn-success btn-sm" style="font-size: 0.78rem; font-weight: 700;">
                                    {{ $isJapanese ? 'Kirim Balasan Sensei' : 'Kirim Balasan Guru' }}
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
                                                            👨‍🏫 {{ $isJapanese ? 'Sensei (Guru Bahasa Jepang)' : 'Balasan Guru' }}
                                                        </span>
                                                    @else
                                                        <span class="badge badge-neutral" style="font-size: 0.68rem;">
                                                            {{ $isJapanese ? ($reply->class_name ?: 'Siswa') : 'Kelas ' . $reply->class_name }}
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
                        @if(!$isJapanese && $selectedClassGroup !== 'all')
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
<!-- PDF.js from CDN with multi-CDN fallback -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js" onerror="this.onerror=null;this.src='https://unpkg.com/pdfjs-dist@3.11.174/build/pdf.min.js';"></script>

<script>
function toggleAdminReplyForm(id) {
    const el = document.getElementById('admin-reply-form-' + id);
    if (!el) return;
    el.style.display = (el.style.display === 'none' || el.style.display === '') ? 'block' : 'none';
}

// ============================================================
// PPT SLIDE PRESENTATION LOGIC + TOUCH SWIPE FOR MOBILE
// ============================================================
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

// Attach Touch Swipe Listener on Slide Stage for iPhone & Android
(function initSlideTouchGestures() {
    const stage = document.getElementById('slideStage');
    if (!stage) return;

    let touchStartX = 0;
    let touchStartY = 0;

    stage.addEventListener('touchstart', function(e) {
        touchStartX = e.changedTouches[0].screenX;
        touchStartY = e.changedTouches[0].screenY;
    }, { passive: true });

    stage.addEventListener('touchend', function(e) {
        const touchEndX = e.changedTouches[0].screenX;
        const touchEndY = e.changedTouches[0].screenY;
        const diffX = touchEndX - touchStartX;
        const diffY = touchEndY - touchStartY;

        // Trigger horizontal swipe only if movement is primarily horizontal and > 35px
        if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 35) {
            if (diffX < 0) {
                nextSlide(); // Geser ke kiri -> Slide berikutnya
            } else {
                prevSlide(); // Geser ke kanan -> Slide sebelumnya
            }
        }
    }, { passive: true });
})();

// ============================================================
// SMART PDF.JS RENDERER (RESOLVES MOBILE/IPHONE STUCK ON COVER)
// ============================================================
const pdfTargetUrl = @json($material->isPdf() ? route('admin.materials.preview', $material) : ($presentationData['pdf_url'] ?? null));
let pdfDocInstance = null;
let currentPdfPage = 1;
let pdfViewMode = 'scroll'; // 'scroll' (all pages) or 'single' (page by page)
let pdfZoomLevel = 1.0;
let isRenderingPdf = false;

function initPdfEngine() {
    if (!pdfTargetUrl) return;

    const loadingEl = document.getElementById('pdfLoadingIndicator');
    const fallbackEl = document.getElementById('pdfNativeFallback');

    // Wait until pdfjsLib is loaded or timeout
    let attempts = 0;
    const checkPdfJs = setInterval(function() {
        attempts++;
        if (typeof pdfjsLib !== 'undefined') {
            clearInterval(checkPdfJs);
            loadPdfWithPdfJs(pdfTargetUrl);
        } else if (attempts > 30) {
            clearInterval(checkPdfJs);
            console.warn('PDF.js tidak dapat dimuat, beralih ke penampil bawaan.');
            if (loadingEl) loadingEl.style.display = 'none';
            if (fallbackEl) fallbackEl.style.display = 'block';
        }
    }, 100);
}

function loadPdfWithPdfJs(url) {
    const loadingEl = document.getElementById('pdfLoadingIndicator');
    const fallbackEl = document.getElementById('pdfNativeFallback');

    try {
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    } catch(e) {}

    const loadingTask = pdfjsLib.getDocument({
        url: url,
        cMapUrl: 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/cmaps/',
        cMapPacked: true,
        enableXfa: true,
    });

    loadingTask.promise.then(function(pdf) {
        pdfDocInstance = pdf;

        const totalEl = document.getElementById('pdfTotalPages');
        if (totalEl) totalEl.innerText = pdf.numPages;

        const selectEl = document.getElementById('pdfPageSelect');
        if (selectEl) {
            selectEl.innerHTML = '';
            for (let i = 1; i <= pdf.numPages; i++) {
                const opt = document.createElement('option');
                opt.value = i;
                opt.innerText = 'Halaman ' + i + ' / ' + pdf.numPages;
                selectEl.appendChild(opt);
            }
        }

        if (loadingEl) loadingEl.style.display = 'none';
        renderPdfDocument();
    }).catch(function(err) {
        console.warn('PDF.js render error:', err);
        if (loadingEl) loadingEl.style.display = 'none';
        if (fallbackEl) fallbackEl.style.display = 'block';
    });
}

function renderPdfDocument() {
    if (!pdfDocInstance) return;
    const container = document.getElementById('pdfPagesContainer');
    if (!container) return;

    container.innerHTML = '';

    if (pdfViewMode === 'scroll') {
        // Continuous scroll: render every page vertically
        for (let p = 1; p <= pdfDocInstance.numPages; p++) {
            renderPageOnCanvas(p, container);
        }
    } else {
        // Single page mode: render active page
        renderPageOnCanvas(currentPdfPage, container);
    }
}

function renderPageOnCanvas(pageNum, container) {
    const pageWrapper = document.createElement('div');
    pageWrapper.className = 'pdf-page-card';
    pageWrapper.id = 'pdf-page-wrap-' + pageNum;
    pageWrapper.style.cssText = 'margin: 0 auto 18px auto; display: flex; flex-direction: column; align-items: center; width: 100%; max-width: 920px;';

    const pageBadge = document.createElement('div');
    pageBadge.style.cssText = 'font-size: 0.74rem; color: #94a3b8; margin-bottom: 6px; font-weight: 700;';
    pageBadge.innerText = '— Halaman ' + pageNum + ' dari ' + pdfDocInstance.numPages + ' —';

    const canvas = document.createElement('canvas');
    canvas.id = 'pdf-canvas-' + pageNum;
    canvas.style.cssText = 'max-width: 100%; height: auto; box-shadow: 0 6px 20px rgba(0,0,0,0.5); border-radius: 6px; background: #ffffff;';

    pageWrapper.appendChild(pageBadge);
    pageWrapper.appendChild(canvas);
    container.appendChild(pageWrapper);

    pdfDocInstance.getPage(pageNum).then(function(page) {
        const stage = document.getElementById('pdfPagesScrollStage');
        const availableWidth = stage ? Math.max(300, stage.clientWidth - 28) : 600;
        const unscaledViewport = page.getViewport({ scale: 1.0 });

        let fitScale = 1.0;
        if (availableWidth > 0 && unscaledViewport.width > 0) {
            fitScale = availableWidth / unscaledViewport.width;
        }

        const effectiveScale = fitScale * pdfZoomLevel;
        const viewport = page.getViewport({ scale: effectiveScale });
        const pixelRatio = window.devicePixelRatio || 1;

        canvas.width = Math.floor(viewport.width * pixelRatio);
        canvas.height = Math.floor(viewport.height * pixelRatio);
        canvas.style.width = Math.floor(viewport.width) + "px";
        canvas.style.height = Math.floor(viewport.height) + "px";

        const ctx = canvas.getContext('2d');
        const transform = pixelRatio !== 1 ? [pixelRatio, 0, 0, pixelRatio, 0, 0] : null;

        page.render({
            canvasContext: ctx,
            transform: transform,
            viewport: viewport
        });
    });
}

function setPdfViewMode(mode) {
    pdfViewMode = mode;
    const btnScroll = document.getElementById('btnPdfModeScroll');
    const btnSingle = document.getElementById('btnPdfModeSingle');
    const singleNav = document.getElementById('pdfSingleNavControls');

    if (mode === 'scroll') {
        if (btnScroll) { btnScroll.style.background = '#2563eb'; btnScroll.style.color = '#fff'; }
        if (btnSingle) { btnSingle.style.background = 'transparent'; btnSingle.style.color = '#cbd5e1'; }
        if (singleNav) singleNav.style.display = 'none';
    } else {
        if (btnScroll) { btnScroll.style.background = 'transparent'; btnScroll.style.color = '#cbd5e1'; }
        if (btnSingle) { btnSingle.style.background = '#2563eb'; btnSingle.style.color = '#fff'; }
        if (singleNav) singleNav.style.display = 'inline-flex';
    }

    renderPdfDocument();
}

function goToPdfPage(num) {
    if (!pdfDocInstance || num < 1 || num > pdfDocInstance.numPages) return;
    currentPdfPage = num;

    const pageNumEl = document.getElementById('pdfCurrentPageNum');
    if (pageNumEl) pageNumEl.innerText = currentPdfPage;

    const selectEl = document.getElementById('pdfPageSelect');
    if (selectEl) selectEl.value = currentPdfPage;

    if (pdfViewMode === 'scroll') {
        const targetPage = document.getElementById('pdf-page-wrap-' + num);
        if (targetPage) {
            targetPage.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    } else {
        renderPdfDocument();
    }
}

function nextPdfPage() {
    if (pdfDocInstance && currentPdfPage < pdfDocInstance.numPages) {
        goToPdfPage(currentPdfPage + 1);
    }
}

function prevPdfPage() {
    if (pdfDocInstance && currentPdfPage > 1) {
        goToPdfPage(currentPdfPage - 1);
    }
}

function zoomPdf(delta) {
    pdfZoomLevel = Math.max(0.6, Math.min(2.5, pdfZoomLevel + delta));
    renderPdfDocument();
}

function resetPdfZoom() {
    pdfZoomLevel = 1.0;
    renderPdfDocument();
}

// Attach Touch Swipe for Single Page PDF Mode
(function initPdfTouchGestures() {
    const stage = document.getElementById('pdfPagesScrollStage');
    if (!stage) return;

    let touchStartX = 0;
    let touchStartY = 0;

    stage.addEventListener('touchstart', function(e) {
        touchStartX = e.changedTouches[0].screenX;
        touchStartY = e.changedTouches[0].screenY;
    }, { passive: true });

    stage.addEventListener('touchend', function(e) {
        if (pdfViewMode !== 'single') return; // Only swipe when in single-page mode

        const touchEndX = e.changedTouches[0].screenX;
        const touchEndY = e.changedTouches[0].screenY;
        const diffX = touchEndX - touchStartX;
        const diffY = touchEndY - touchStartY;

        if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 40) {
            if (diffX < 0) {
                nextPdfPage();
            } else {
                prevPdfPage();
            }
        }
    }, { passive: true });
})();

// Auto-run PDF engine if document is PDF
document.addEventListener('DOMContentLoaded', function() {
    if (pdfTargetUrl) {
        initPdfEngine();
    }
});

// Re-render PDF on device orientation change (portrait <-> landscape on mobile/tablet)
window.addEventListener('resize', (function() {
    let resizeTimer;
    return function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            if (pdfDocInstance) renderPdfDocument();
        }, 250);
    };
})());

// ============================================================
// EMBED / OFFICE / GOOGLE SWITCHER
// ============================================================
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

// Keyboard shortcuts for desktop
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

// Fullscreen Toggle (Supports standard Fullscreen API + iOS Safari Pseudo-Fullscreen)
function toggleViewerFullscreen() {
    const el = document.getElementById('materialViewerCard');
    const exitBtn = document.getElementById('mobileFullscreenExitBtn');

    // Check if on iOS / Mobile where requestFullscreen is unsupported or fails
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);

    if (isIOS || !document.fullscreenEnabled) {
        // Toggle CSS Pseudo-Fullscreen
        el.classList.toggle('viewer-mobile-fullscreen');
        const isNowFs = el.classList.contains('viewer-mobile-fullscreen');
        if (exitBtn) exitBtn.style.display = isNowFs ? 'block' : 'none';
        document.body.style.overflow = isNowFs ? 'hidden' : '';
        if (pdfDocInstance) setTimeout(renderPdfDocument, 100);
        return;
    }

    if (!document.fullscreenElement) {
        if (el.requestFullscreen) {
            el.requestFullscreen().catch(function() {
                el.classList.add('viewer-mobile-fullscreen');
                if (exitBtn) exitBtn.style.display = 'block';
            });
        }
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        }
    }
}

document.addEventListener('fullscreenchange', function() {
    const exitBtn = document.getElementById('mobileFullscreenExitBtn');
    if (!document.fullscreenElement) {
        const el = document.getElementById('materialViewerCard');
        el.classList.remove('viewer-mobile-fullscreen');
        if (exitBtn) exitBtn.style.display = 'none';
        document.body.style.overflow = '';
    }
});
</script>

<style>
@keyframes fadeIn {
    from { opacity: 0; transform: scale(0.99); }
    to { opacity: 1; transform: scale(1); }
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.slide-thumb-card:hover {
    border-color: #818cf8 !important;
}

/* Mobile Slide Overlay Arrows */
.mobile-slide-tap-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 44px;
    height: 64px;
    background: rgba(15, 23, 42, 0.45);
    color: #ffffff;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 6px;
    font-size: 2.2rem;
    font-weight: 300;
    line-height: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 10;
    transition: all 0.2s;
    backdrop-filter: blur(2px);
    -webkit-tap-highlight-color: transparent;
}
.mobile-slide-tap-arrow.left {
    left: 10px;
}
.mobile-slide-tap-arrow.right {
    right: 10px;
}
.mobile-slide-tap-arrow:hover, .mobile-slide-tap-arrow:active {
    background: rgba(37, 99, 235, 0.85);
    transform: translateY(-50%) scale(1.05);
}

/* Mobile Helper Banner */
@media (max-width: 992px) {
    .mobile-material-banner {
        display: flex !important;
    }
}

/* Responsive adjustments for phones: iPhone SE (375px), iPhone 16 (393px), iPhone Pro Max (430px) */
@media (max-width: 768px) {
    #slideStage {
        min-height: 240px !important;
        max-height: 52vh !important;
        padding: 8px !important;
    }
    .real-slide-item img {
        max-height: 48vh !important;
    }
    .mobile-slide-tap-arrow {
        width: 36px !important;
        height: 52px !important;
        font-size: 1.8rem !important;
    }
    #pdfPagesScrollStage {
        min-height: 380px !important;
        max-height: 75vh !important;
        padding: 8px 4px !important;
    }
    .slide-thumb-card {
        min-width: 85px !important;
        padding: 4px !important;
    }
    .slide-thumb-card img {
        width: 78px !important;
        height: 44px !important;
    }
}

/* Fullscreen Styles (Desktop API) */
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
#materialViewerCard:fullscreen #slideModeView,
#materialViewerCard:fullscreen #pdfSmartViewerSection {
    flex: 1;
    display: flex;
    flex-direction: column;
}
#materialViewerCard:fullscreen #slideStage {
    flex: 1;
    max-height: none !important;
    min-height: calc(100vh - 180px) !important;
}
#materialViewerCard:fullscreen #pdfPagesScrollStage {
    flex: 1;
    max-height: none !important;
    min-height: calc(100vh - 110px) !important;
}
#materialViewerCard:fullscreen .real-slide-item img {
    max-height: calc(100vh - 200px) !important;
}

/* Pseudo-Fullscreen Styles (for iOS Safari on iPhone & iPad) */
.viewer-mobile-fullscreen {
    position: fixed !important;
    inset: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    z-index: 999999 !important;
    border-radius: 0 !important;
    margin: 0 !important;
    background: #0f172a !important;
    display: flex !important;
    flex-direction: column !important;
}
.viewer-mobile-fullscreen #viewerContainer {
    flex: 1 !important;
    display: flex !important;
    flex-direction: column !important;
}
.viewer-mobile-fullscreen #slideStage {
    flex: 1 !important;
    min-height: calc(100vh - 180px) !important;
    max-height: none !important;
}
.viewer-mobile-fullscreen #pdfPagesScrollStage {
    flex: 1 !important;
    min-height: calc(100vh - 120px) !important;
    max-height: none !important;
}
.viewer-mobile-fullscreen .real-slide-item img {
    max-height: calc(100vh - 200px) !important;
}
</style>
@endpush
@endsection
