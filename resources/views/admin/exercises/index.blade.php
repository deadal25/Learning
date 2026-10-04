@extends('layouts.app')

@section('title', 'Bank Soal Latihan (10 Soal/Level) - Musashi Learning')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.8rem; margin-bottom: 0.25rem;">Bank Soal Latihan (10 Soal / Level)</h1>
        <p style="color: var(--text-muted); font-size: 0.92rem;">
            Tiap level memiliki 10 butir soal latihan. Jawaban benar memberi +10 poin (mencapai 100 poin membuka level berikutnya).
        </p>
    </div>
    <a href="{{ route('admin.exercises.create', ['level_id' => request('level_id')]) }}" class="btn btn-primary">
        + Buat Butir Soal Baru
    </a>
</div>

<!-- Filter Bar -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body" style="padding: 1rem 1.5rem;">
        <form method="GET" action="{{ route('admin.exercises.index') }}" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 220px;">
                <input type="text" name="search" class="form-control" placeholder="Cari teks soal atau penjelasan..." value="{{ request('search') }}">
            </div>
            <div>
                <select name="subject_id" class="form-control" onchange="this.form.submit()">
                    <option value="">Semua Mapel</option>
                    @foreach($subjects as $subj)
                        <option value="{{ $subj->id }}" {{ request('subject_id') == $subj->id ? 'selected' : '' }}>
                            {{ $subj->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="level_id" class="form-control" onchange="this.form.submit()">
                    <option value="">Semua Tingkatan Level</option>
                    @foreach($subjects as $subj)
                        <optgroup label="{{ $subj->name }}">
                            @foreach($subj->levels as $lvl)
                                <option value="{{ $lvl->id }}" {{ request('level_id') == $lvl->id ? 'selected' : '' }}>
                                    {{ $subj->name }} - {{ $lvl->name }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-secondary">Filter</button>
            @if(request()->hasAny(['search', 'subject_id', 'level_id']))
                <a href="{{ route('admin.exercises.index') }}" class="btn btn-secondary" style="color: var(--text-muted);">Reset</a>
            @endif
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 60px;">No</th>
                        <th>Pertanyaan & Opsi Jawaban</th>
                        <th>Mapel & Level</th>
                        <th>Kunci Benar</th>
                        <th>Poin</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($exercises as $ex)
                        <tr>
                            <td>
                                <div style="width: 30px; height: 30px; border-radius: var(--radius-full); background: var(--bg-surface-alt); display: flex; align-items: center; justify-content: center; font-weight: 700;">
                                    #{{ $ex->question_number }}
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #0f172a; margin-bottom: 6px; font-size: 0.95rem;">
                                    {{ $ex->question }}
                                </div>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 6px; font-size: 0.8rem; background: #f8fafc; padding: 8px 12px; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                                    <span style="{{ $ex->correct_option === 'a' ? 'font-weight: 700; color: #059669;' : 'color: #475569;' }}">
                                        <strong>A:</strong> {{ Str::limit($ex->option_a, 40) }} {{ $ex->correct_option === 'a' ? '✓' : '' }}
                                    </span>
                                    <span style="{{ $ex->correct_option === 'b' ? 'font-weight: 700; color: #059669;' : 'color: #475569;' }}">
                                        <strong>B:</strong> {{ Str::limit($ex->option_b, 40) }} {{ $ex->correct_option === 'b' ? '✓' : '' }}
                                    </span>
                                    <span style="{{ $ex->correct_option === 'c' ? 'font-weight: 700; color: #059669;' : 'color: #475569;' }}">
                                        <strong>C:</strong> {{ Str::limit($ex->option_c, 40) }} {{ $ex->correct_option === 'c' ? '✓' : '' }}
                                    </span>
                                    <span style="{{ $ex->correct_option === 'd' ? 'font-weight: 700; color: #059669;' : 'color: #475569;' }}">
                                        <strong>D:</strong> {{ Str::limit($ex->option_d, 40) }} {{ $ex->correct_option === 'd' ? '✓' : '' }}
                                    </span>
                                </div>
                                @if($ex->explanation)
                                    <div style="font-size: 0.78rem; color: #64748b; margin-top: 4px; font-style: italic;">
                                        💡 Pembahasan: {{ Str::limit($ex->explanation, 80) }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div>
                                    <span class="badge" style="background: {{ $ex->level->subject->badge_color }}15; color: {{ $ex->level->subject->badge_color }};">
                                        {{ $ex->level->subject->name }}
                                    </span>
                                </div>
                                <div style="font-size: 0.8rem; font-weight: 600; color: #334155; margin-top: 4px;">
                                    {{ $ex->level->name }}
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-success" style="font-size: 0.85rem; padding: 4px 10px;">
                                    Opsi {{ strtoupper($ex->correct_option) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-warning">
                                    +{{ $ex->points }} Pts
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 6px;">
                                    <a href="{{ route('admin.exercises.edit', $ex) }}" class="btn btn-secondary btn-sm">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.exercises.destroy', $ex) }}" method="POST" onsubmit="return confirm('Hapus butir soal latihan ini?')">
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
                            <td colspan="6" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                                Belum ada soal latihan pada kategori ini. Klik "+ Buat Butir Soal Baru".
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($exercises->hasPages())
        <div class="card-footer">
            {{ $exercises->links() }}
        </div>
    @endif
</div>
@endsection
