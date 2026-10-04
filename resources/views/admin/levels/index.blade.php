@extends('layouts.app')

@section('title', 'Tingkatan Level: ' . $subject->name)

@section('content')
<div style="margin-bottom: 1.5rem;">
    <a href="{{ route('admin.subjects.index') }}" class="btn btn-secondary btn-sm" style="margin-bottom: 0.75rem;">
        &larr; Kembali ke Daftar Mata Pelajaran
    </a>
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="badge" style="background: {{ $subject->badge_color }}20; color: {{ $subject->badge_color }};">Mata Pelajaran</span>
                <h1 style="font-size: 1.8rem; margin: 0;">{{ $subject->name }} - Jenjang Tingkatan Level</h1>
            </div>
            <p style="color: var(--text-muted); font-size: 0.92rem; margin-top: 4px;">
                Setiap level berisi materi slide PPT dan 10 latihan soal (+10 poin/jawaban). Siswa membutuhkan 100 poin untuk naik level.
            </p>
        </div>
        <a href="{{ route('admin.subjects.levels.create', $subject) }}" class="btn btn-primary">
            + Tambah Tingkatan / Level Baru
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 80px;">Urutan</th>
                        <th>Nama Tingkatan (Level)</th>
                        <th>Deskripsi Kompetensi</th>
                        <th>Syarat Naik Level</th>
                        <th>Materi Terunggah</th>
                        <th>Soal Latihan</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subject->levels as $level)
                        <tr>
                            <td>
                                <div style="width: 32px; height: 32px; border-radius: var(--radius-full); background: var(--color-primary-light); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-weight: 800;">
                                    {{ $level->order }}
                                </div>
                            </td>
                            <td>
                                <strong style="font-size: 1.02rem; color: #0f172a;">{{ $level->name }}</strong>
                            </td>
                            <td>
                                <span style="color: var(--text-muted); font-size: 0.88rem;">
                                    {{ $level->description ?? '-' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-warning">
                                    {{ $level->required_points }} Poin
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.materials.index', ['level_id' => $level->id]) }}" class="badge badge-primary">
                                    📑 {{ $level->materials_count }} Slide
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('admin.exercises.index', ['level_id' => $level->id]) }}" class="badge {{ $level->exercises_count >= 10 ? 'badge-success' : 'badge-neutral' }}">
                                    🎯 {{ $level->exercises_count }} / 10 Soal
                                </a>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 6px;">
                                    <a href="{{ route('admin.exercises.index', ['level_id' => $level->id]) }}" class="btn btn-secondary btn-sm" title="Kelola 10 Soal">
                                        Soal
                                    </a>
                                    <a href="{{ route('admin.subjects.levels.edit', [$subject, $level]) }}" class="btn btn-secondary btn-sm">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.subjects.levels.destroy', [$subject, $level]) }}" method="POST" onsubmit="return confirm('Hapus tingkatan level ini beserta seluruh materi dan latihan di dalamnya?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                                Belum ada tingkatan level pada mata pelajaran ini. Klik "+ Tambah Tingkatan / Level Baru".
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
