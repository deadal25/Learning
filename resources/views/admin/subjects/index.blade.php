@extends('layouts.app')

@section('title', 'Mata Pelajaran & Level - Musashi Learning')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.8rem; margin-bottom: 0.25rem;">Mata Pelajaran & Tingkatan Level</h1>
        <p style="color: var(--text-muted); font-size: 0.92rem;">
            Kelola mata pelajaran dan jenjang tingkatan bertingkat (Basic, Beginner, Middle, Advanced, dll).
        </p>
    </div>
    <a href="{{ route('admin.subjects.create') }}" class="btn btn-primary">
        + Tambah Mata Pelajaran Baru
    </a>
</div>

<div class="grid grid-cols-3">
    @forelse($subjects as $subject)
        <div class="card">
            <div class="card-header" style="background: {{ $subject->badge_color }}10;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 42px; height: 42px; border-radius: var(--radius-md); background: {{ $subject->badge_color }}; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; font-weight: 700;">
                        {{ substr($subject->name, 0, 1) }}
                    </div>
                    <div>
                        <h3 style="font-size: 1.15rem; margin-bottom: 2px;">{{ $subject->name }}</h3>
                        <span class="badge" style="background: {{ $subject->badge_color }}20; color: {{ $subject->badge_color }};">
                            Urutan #{{ $subject->order }}
                        </span>
                    </div>
                </div>
                <div style="display: flex; gap: 6px;">
                    <a href="{{ route('admin.subjects.edit', $subject) }}" class="btn btn-secondary btn-sm" title="Edit Mapel">
                        Edit
                    </a>
                </div>
            </div>
            <div class="card-body">
                <p style="font-size: 0.88rem; color: var(--text-muted); margin-bottom: 1.25rem; min-height: 48px;">
                    {{ $subject->description ?? 'Tidak ada deskripsi.' }}
                </p>

                <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 12px; margin-bottom: 1.25rem;">
                    <div style="display: flex; justify-content: space-between; font-size: 0.82rem; margin-bottom: 4px;">
                        <span style="color: var(--text-muted);">Jumlah Tingkatan:</span>
                        <strong>{{ $subject->levels->count() }} Level</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.82rem; margin-bottom: 4px;">
                        <span style="color: var(--text-muted);">Total Materi:</span>
                        <strong>{{ $subject->levels->sum('materials_count') }} Slide/Dokumen</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.82rem;">
                        <span style="color: var(--text-muted);">Total Soal Latihan:</span>
                        <strong>{{ $subject->levels->sum('exercises_count') }} Soal (10/Level)</strong>
                    </div>
                </div>

                <div style="display: flex; gap: 8px;">
                    <a href="{{ route('admin.subjects.levels.index', $subject) }}" class="btn btn-primary btn-sm" style="flex: 1;">
                        ⚙️ Atur Level Bertingkat &rarr;
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div style="grid-column: 1 / -1;" class="card">
            <div class="card-body" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                Belum ada mata pelajaran. Klik "+ Tambah Mata Pelajaran Baru" untuk memulai.
            </div>
        </div>
    @endforelse
</div>
@endsection
