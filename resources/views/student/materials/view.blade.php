@extends('layouts.app')

@section('title', $material->title . ' - Musashi Learning')

@section('content')
<div style="margin-bottom: 1.5rem;">
    <div style="display: flex; gap: 10px; margin-bottom: 0.75rem; flex-wrap: wrap;">
        <a href="{{ route('student.materials.index') }}" class="btn btn-secondary btn-sm">
            &larr; Katalog Materi
        </a>
        <a href="{{ route('student.subjects.show', $level->subject_id) }}" class="btn btn-secondary btn-sm">
            Mata Pelajaran {{ $level->subject->name }}
        </a>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px; flex-wrap: wrap;">
                <span class="badge" style="background: #e0e7ff; color: #3730a3; font-weight: 800; font-size: 0.85rem; padding: 5px 12px;">
                    🗓️ {{ $level->name }}
                </span>
                @if($material->class_name)
                    <span class="badge" style="background: #eff6ff; color: #1e40af; font-weight: 800; font-size: 0.85rem; padding: 5px 12px;">
                        🎓 {{ $material->class_name }}
                    </span>
                @endif
                <span class="badge badge-neutral">{{ $material->file_type_label }}</span>
            </div>
            <h1 style="font-size: 1.85rem; margin: 0; color: #0f172a;">{{ $material->title }}</h1>
        </div>

        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            @if($material->file_path)
                <a href="{{ route('student.materials.download', $material) }}" class="btn btn-secondary" title="Unduh file materi asli">
                    ⬇ Unduh File ({{ strtoupper($material->file_type ?? 'FILE') }})
                </a>
            @endif
            <a href="{{ route('student.exercises.show', $level) }}" class="btn btn-primary">
                Kerjakan Soal Latihan &rarr;
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
            @if(!empty($presentationData['has_slides']) && !empty($presentationData['pdf_url']))
                <!-- View Mode Toggle (Slide vs PDF) -->
                <div style="display: inline-flex; background: rgba(255,255,255,0.1); border-radius: 6px; padding: 3px; border: 1px solid rgba(255,255,255,0.2);">
                    <button type="button" id="btnModeSlides" onclick="switchViewMode('slides')" class="btn btn-sm" style="background: #4f46e5; color: #fff; border: none; font-size: 0.8rem; font-weight: 600; padding: 4px 10px;">
                        🖥️ Slide Presentasi
                    </button>
                    <button type="button" id="btnModePdf" onclick="switchViewMode('pdf')" class="btn btn-sm" style="background: transparent; color: #cbd5e1; border: none; font-size: 0.8rem; font-weight: 600; padding: 4px 10px;">
                        📄 Dokumen PDF Asli
                    </button>
                </div>
            @endif

            @if($material->file_path)
                <a href="{{ route('student.materials.preview', $material) }}" target="_blank" class="btn btn-secondary btn-sm" style="background: rgba(255,255,255,0.15); color: #fff; border: 1px solid rgba(255,255,255,0.25);" title="Buka pratinjau dokumen / slide di tab baru browser">
                    ↗ Buka di Tab Baru
                </a>
                <a href="{{ route('student.materials.download', $material) }}" class="btn btn-warning btn-sm" style="font-weight: 600;">
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
        @if(!empty($presentationData['has_slides']))
            <!-- REAL SLIDE PRESENTATION VIEW (Extracted directly from the real PPT/PDF file) -->
            <div id="slideModeView" style="display: block;">
                <!-- Sub-toolbar: Slide navigation controls -->
                <div style="background: #1e293b; padding: 10px 18px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span class="badge badge-warning" style="font-weight: 700; font-size: 0.82rem;">📊 Slide Dokumen Asli</span>
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
                        <a href="{{ route('student.materials.download', $material) }}" class="btn btn-warning btn-sm" style="font-weight: 600;">
                            ⬇ Unduh Berkas Asli ({{ strtoupper($material->file_type ?? 'FILE') }})
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

        @elseif($material->file_path && $material->isPdf())
            <!-- Native In-Browser PDF Viewer for PDF files -->
            <div style="height: 750px; width: 100%; background: #525659;">
                <object data="{{ route('student.materials.preview', $material) }}" type="application/pdf" width="100%" height="100%">
                    <iframe src="{{ route('student.materials.preview', $material) }}#toolbar=1&navpanes=1" width="100%" height="100%" style="border: none; display: block;" title="{{ $material->title }}">
                        <div style="padding: 3rem; text-align: center; color: #ffffff;">
                            <p style="margin-bottom: 1rem;">Dokumen PDF materi siap dipelajari.</p>
                            <a href="{{ route('student.materials.preview', $material) }}" target="_blank" class="btn btn-primary" style="margin-right: 8px;">
                                ↗ Buka PDF di Tab Baru
                            </a>
                            <a href="{{ route('student.materials.download', $material) }}" class="btn btn-warning">
                                Unduh File PDF Materi
                            </a>
                        </div>
                    </iframe>
                </object>
            </div>

        @elseif($material->file_path && $material->isImage())
            <!-- Original Image Viewer -->
            <div style="padding: 2rem; text-align: center; background: #0f172a; min-height: 450px; display: flex; align-items: center; justify-content: center;">
                <img src="{{ route('student.materials.preview', $material) }}" alt="{{ $material->title }}" style="max-width: 100%; max-height: 720px; object-fit: contain; border-radius: var(--radius-md); box-shadow: 0 10px 25px rgba(0,0,0,0.5);">
            </div>

        @elseif($material->file_path && $material->isPpt())
            <!-- Standby fallback for PPT / PPTX file -->
            <div style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%); color: #ffffff; padding: 4rem 2rem; text-align: center;">
                <div style="max-width: 600px; margin: 0 auto;">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">📊</div>
                    <h2 style="font-size: 1.6rem; color: #ffffff; margin-bottom: 0.5rem;">{{ $material->file_name ?? $material->title }}</h2>
                    <p style="color: #cbd5e1; margin-bottom: 1.5rem; font-size: 0.95rem;">
                        File presentasi PowerPoint telah siap. Anda dapat mengunduh berkas materi untuk membukanya langsung.
                    </p>
                    <a href="{{ route('student.materials.download', $material) }}" class="btn btn-warning btn-lg" style="font-weight: 700;">
                        ⬇ Unduh Berkas Presentasi PPTX
                    </a>
                </div>
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
            <h2 style="font-size: 1.15rem; margin: 0;">📖 Teks / Catatan Tambahan dari Guru</h2>
        </div>
        <div class="card-body" style="font-size: 1rem; line-height: 1.8; color: #334155;">
            {!! nl2br(e($material->content)) !!}
        </div>
    </div>
@endif

<!-- Other Materials in this Level -->
@if($otherMaterials->isNotEmpty())
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-header">
            <h3 style="font-size: 1.15rem; margin: 0;">Materi Lain di {{ $level->name }}</h3>
        </div>
        <div class="card-body" style="padding: 1rem;">
            <div style="display: flex; flex-direction: column; gap: 8px;">
                @foreach($otherMaterials as $om)
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; background: #f8fafc; border-radius: var(--radius-md); border: 1px solid #e2e8f0; flex-wrap: wrap; gap: 10px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="font-size: 1.3rem;">{{ $om->file_icon }}</span>
                            <div>
                                <strong style="color: #0f172a; font-size: 0.92rem;">{{ $om->title }}</strong>
                                <div style="font-size: 0.78rem; color: var(--text-muted);">{{ $om->file_type_label }}</div>
                            </div>
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <a href="{{ route('student.materials.view', $om) }}" class="btn btn-secondary btn-sm">
                                Buka Materi &rarr;
                            </a>
                            @if($om->file_path)
                                <a href="{{ route('student.materials.download', $om) }}" class="btn btn-secondary btn-sm" title="Unduh">
                                    ⬇
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif

<!-- Ready to practice CTA banner -->
<div class="card" style="background: linear-gradient(to right, #eff6ff, #f0fdf4); border: 1px solid #bfdbfe; margin-bottom: 2rem;">
    <div class="card-body" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1.25rem; padding: 1.5rem;">
        <div>
            <h3 style="font-size: 1.2rem; color: #1e1b4b; margin-bottom: 4px;">Sudah selesai mempelajari materi ini?</h3>
            <p style="font-size: 0.9rem; color: var(--text-muted); margin: 0; max-width: 600px;">
                Uji pemahamanmu dengan menyelesaikan 10 butir soal latihan pada <strong>{{ $level->name }}</strong>. Capai 100 poin untuk membuka level berikutnya!
            </p>
        </div>
        <a href="{{ route('student.exercises.show', $level) }}" class="btn btn-primary btn-lg" style="font-weight: 700;">
            Mulai Kerjakan 10 Soal Latihan &rarr;
        </a>
    </div>
</div>

@push('scripts')
<script>
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

function switchViewMode(mode) {
    const slideView = document.getElementById('slideModeView');
    const pdfView = document.getElementById('pdfModeView');
    const btnSlides = document.getElementById('btnModeSlides');
    const btnPdf = document.getElementById('btnModePdf');

    if (mode === 'slides' && slideView) {
        slideView.style.display = 'block';
        if (pdfView) pdfView.style.display = 'none';
        if (btnSlides) {
            btnSlides.style.background = '#4f46e5';
            btnSlides.style.color = '#ffffff';
        }
        if (btnPdf) {
            btnPdf.style.background = 'transparent';
            btnPdf.style.color = '#cbd5e1';
        }
    } else if (mode === 'pdf' && pdfView) {
        if (slideView) slideView.style.display = 'none';
        pdfView.style.display = 'block';
        if (btnPdf) {
            btnPdf.style.background = '#4f46e5';
            btnPdf.style.color = '#ffffff';
        }
        if (btnSlides) {
            btnSlides.style.background = 'transparent';
            btnSlides.style.color = '#cbd5e1';
        }
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
#materialViewerCard:fullscreen .real-slide-item img {
    max-height: calc(100vh - 200px) !important;
}
</style>
@endpush
@endsection
