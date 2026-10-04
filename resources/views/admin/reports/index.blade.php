@extends('layouts.app')

@section('title', 'Laporan Progres Belajar Siswa - Musashi Learning')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.8rem; margin-bottom: 0.25rem;">Laporan Rekapitulasi Progres Siswa</h1>
        <p style="color: var(--text-muted); font-size: 0.92rem;">
            Matriks pemantauan tingkatan level dan poin pencapaian siswa di setiap mata pelajaran.
        </p>
    </div>
    <button onclick="window.print()" class="btn btn-secondary">
        🖨️ Cetak / Unduh Laporan
    </button>
</div>

<!-- Search -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body" style="padding: 1rem 1.5rem;">
        <form method="GET" action="{{ route('admin.reports.index') }}" style="display: flex; gap: 12px;">
            <div style="flex: 1;">
                <input type="text" name="search" class="form-control" placeholder="Cari nama atau email siswa..." value="{{ request('search') }}">
            </div>
            <button type="submit" class="btn btn-secondary">Cari Siswa</button>
            @if(request('search'))
                <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary" style="color: var(--text-muted);">Reset</a>
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
                        <th>No</th>
                        <th>Nama Siswa</th>
                        @foreach($subjects as $subj)
                            <th>{{ $subj->name }}</th>
                        @endforeach
                        <th>Total Poin Terkumpul</th>
                        <th style="text-align: right;">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $index => $student)
                        @php
                            $totalPts = $student->levelStatuses->sum('points');
                        @endphp
                        <tr>
                            <td>{{ $students->firstItem() + $index }}</td>
                            <td>
                                <strong>{{ $student->name }}</strong>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $student->email }}</div>
                            </td>
                            @foreach($subjects as $subj)
                                @php
                                    $prog = $student->progresses->firstWhere('subject_id', $subj->id);
                                    $lvl = $prog?->currentLevel;
                                    $pts = $prog?->current_points ?? 0;
                                @endphp
                                <td>
                                    @if($lvl)
                                        <div style="font-weight: 700; font-size: 0.88rem; color: #0f172a;">
                                            {{ $lvl->name }}
                                        </div>
                                        <div style="font-size: 0.78rem; color: {{ $pts >= 100 ? '#059669' : '#4f46e5' }}; font-weight: 600;">
                                            {{ $pts }}/100 Poin
                                            @if($pts >= 100)
                                                <span class="badge badge-success" style="font-size: 0.65rem;">Siap Naik</span>
                                            @endif
                                        </div>
                                    @else
                                        <span style="color: var(--text-muted); font-size: 0.82rem;">Belum Mulai</span>
                                    @endif
                                </td>
                            @endforeach
                            <td>
                                <span class="badge badge-warning" style="font-size: 0.85rem; font-weight: 800;">
                                    🏆 {{ $totalPts }} Pts
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <a href="{{ route('admin.students.show', $student) }}" class="btn btn-secondary btn-sm">
                                    Rapor Lengkap &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 4 + $subjects->count() }}" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                                Belum ada data progres siswa.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($students->hasPages())
        <div class="card-footer">
            {{ $students->links() }}
        </div>
    @endif
</div>
@endsection
