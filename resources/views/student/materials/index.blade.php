@extends('layouts.app')

@php
    $activeSubj = $currentSubject ?? (auth()->user()->subject ?: \App\Models\Subject::find(1));
    $subjName = $activeSubj?->name ?? 'Pembelajaran';
    $classLabel = (auth()->user()->subject_id == 2 ? 'Grup: ' : 'Kelas: ') . (auth()->user()->class_name ?: 'Reguler');
@endphp

@section('title', 'Materi ' . $subjName . ' (' . $classLabel . ') - Musashi Learning')

@section('content')
<div style="margin-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                <span class="badge badge-primary" style="font-size: 0.8rem; font-weight: 700;">
                    🎓 {{ $classLabel }}
                </span>
                <span class="badge" style="background: {{ (auth()->user()->subject_id == 2) ? '#fdf2f8' : ((auth()->user()->subject_id == 3) ? '#ecfdf5' : '#eff6ff') }}; color: {{ (auth()->user()->subject_id == 2) ? '#be185d' : ((auth()->user()->subject_id == 3) ? '#047857' : '#1e40af') }}; font-size: 0.8rem; font-weight: 700;">
                    📚 {{ $subjName }}
                </span>
                @if(auth()->user()->division)
                    <span class="badge" style="background: #f1f5f9; color: #334155; font-size: 0.8rem;">
                        🏢 {{ auth()->user()->division }}
                    </span>
                @endif
            </div>
            <h1 style="font-size: 1.85rem; margin: 0; font-weight: 800; color: #0f172a;">
                Materi Belajar {{ $subjName }} - {{ $classLabel }}
            </h1>
            <p style="color: var(--text-muted); font-size: 0.92rem; margin-top: 4px;">
                Anda hanya dapat mengakses dan mempelajari modul presentasi slide yang ditujukan khusus untuk kelas {{ $subjName }} Anda.
            </p>
        </div>
        <div>
            <a href="{{ route('student.dashboard') }}" class="btn btn-secondary btn-sm">
                &larr; Kembali ke Beranda
            </a>
        </div>
    </div>
</div>

<!-- Search Bar -->
<div class="card" style="margin-bottom: 2rem; box-shadow: var(--shadow-sm);">
    <div class="card-body" style="padding: 1.25rem;">
        <form action="{{ route('student.materials.index') }}" method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
            <div style="flex: 1; min-width: 260px;">
                <input type="text" name="search" id="search" class="form-control" value="{{ request('search') }}" placeholder="Cari judul materi atau topik pembahasan...">
            </div>

            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary" style="padding: 10px 20px; font-weight: 700;">
                    Cari Materi
                </button>
                @if(request('search'))
                    <a href="{{ route('student.materials.index') }}" class="btn btn-secondary" style="padding: 10px 14px;">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Materials Grid -->
@if($materials->isNotEmpty())
    <div class="grid grid-cols-3" style="gap: 1.5rem; margin-bottom: 2.5rem;">
        @foreach($materials as $material)
            @php
                $isPpt = $material->isPpt();
                $isPdf = $material->isPdf();
                $borderColor = $isPpt ? '#4f46e5' : ($isPdf ? '#e11d48' : '#2563eb');
            @endphp
            <div class="card" style="display: flex; flex-direction: column; justify-content: space-between; border-top: 4px solid {{ $borderColor }}; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); transition: transform 0.2s, box-shadow 0.2s;">
                <div>
                    <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 1rem 1.25rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px; flex-wrap: wrap;">
                            <div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">
                                <span class="badge" style="background: #eff6ff; color: #1e40af; font-size: 0.78rem; font-weight: 800;">
                                    🎓 {{ $material->class_name ?: 'Semua Kelas' }}
                                </span>
                                <span class="badge" style="background: #fdf4ff; color: #a21caf; font-size: 0.78rem; font-weight: 800; border: 1px solid #f0abfc;">
                                    🗓️ {{ $material->level->name }}
                                </span>
                            </div>
                            @if($isPpt)
                                <span class="badge" style="background: #fef3c7; color: #92400e; font-size: 0.75rem; font-weight: 800; border: 1px solid #fde68a;">
                                    📊 Slide PPT
                                </span>
                            @elseif($isPdf)
                                <span class="badge" style="background: #fee2e2; color: #991b1b; font-size: 0.75rem; font-weight: 800; border: 1px solid #fecaca;">
                                    📕 Dokumen PDF
                                </span>
                            @else
                                <span class="badge badge-neutral" style="font-size: 0.72rem; font-weight: 700;">
                                    {{ strtoupper($material->file_type ?? 'Slide') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="card-body" style="padding: 1.25rem;">
                        <div style="display: flex; align-items: flex-start; gap: 12px; margin-bottom: 10px;">
                            <div style="width: 44px; height: 44px; border-radius: var(--radius-md); background: {{ $isPpt ? '#eef2ff' : ($isPdf ? '#fff1f2' : '#eff6ff') }}; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; flex-shrink: 0;">
                                {{ $material->file_icon }}
                            </div>
                            <div style="flex: 1;">
                                <h3 style="font-size: 1.05rem; margin: 0 0 4px; color: #0f172a; line-height: 1.35; font-weight: 800;">
                                    {{ $material->title }}
                                </h3>
                                @if($material->file_name)
                                    <span style="font-size: 0.74rem; color: var(--text-muted); font-family: monospace; display: block;">
                                        📁 {{ Str::limit($material->file_name, 30) }}
                                    </span>
                                @endif
                                @if($material->teacher)
                                    <span style="font-size: 0.74rem; color: #4338ca; font-weight: 600; display: block; margin-top: 2px;">
                                        👤 Guru: {{ $material->teacher->name }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        @if($material->description)
                            <p style="font-size: 0.85rem; color: #475569; line-height: 1.5; margin-bottom: 12px;">
                                {{ Str::limit($material->description, 110) }}
                            </p>
                        @endif

                        @if($material->content)
                            <div style="background: #f8fafc; border-radius: var(--radius-sm); padding: 8px 12px; font-size: 0.8rem; color: #334155; margin-bottom: 10px; border-left: 3px solid var(--color-primary);">
                                📖 <strong>Ada Catatan Materi:</strong> {{ Str::limit(strip_tags($material->content), 80) }}
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card-footer" style="background: #f8fafc; padding: 0.9rem 1.25rem; display: flex; gap: 8px; justify-content: space-between;">
                    <a href="{{ route('student.materials.view', $material) }}" class="btn {{ $isPpt ? 'btn-primary' : ($isPdf ? 'btn-danger' : 'btn-primary') }} btn-sm" style="flex: 1; text-align: center; font-weight: 700; {{ $isPdf ? 'background: #e11d48; border-color: #e11d48; color: #fff;' : '' }}">
                        @if($isPpt)
                            🖥️ Buka Slide PPT &rarr;
                        @elseif($isPdf)
                            📄 Buka Dokumen PDF &rarr;
                        @else
                            Buka & Pelajari Materi &rarr;
                        @endif
                    </a>
                    @if($material->file_path)
                        <a href="{{ route('student.materials.download', $material) }}" class="btn btn-secondary btn-sm" title="Unduh File Materi">
                            ⬇ Unduh
                        </a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    @if($materials->hasPages())
        <div style="margin-bottom: 2rem;">
            {{ $materials->links() }}
        </div>
    @endif
@else
    <div class="card" style="text-align: center; padding: 4rem 2rem; border-radius: var(--radius-lg);">
        <div style="font-size: 3.5rem; margin-bottom: 1rem;">📚</div>
        <h2 style="font-size: 1.4rem; margin-bottom: 0.5rem; color: #0f172a; font-weight: 800;">
            Belum Ada Materi untuk Kelas {{ auth()->user()->class_name ?: 'Anda' }}
        </h2>
        <p style="color: var(--text-muted); max-width: 480px; margin: 0 auto 1.5rem; font-size: 0.92rem;">
            Guru belum menambahkan materi khusus untuk kelas ini. Begitu guru mengunggah modul atau slide presentasi, materi akan otomatis tampil di sini.
        </p>
        <a href="{{ route('student.dashboard') }}" class="btn btn-secondary">
            &larr; Kembali ke Beranda
        </a>
    </div>
@endif
@endsection
