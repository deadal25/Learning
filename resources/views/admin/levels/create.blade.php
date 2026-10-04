@extends('layouts.app')

@section('title', 'Tambah Level Baru - ' . $subject->name)

@section('content')
<div style="max-width: 680px; margin: 0 auto;">
    <div style="margin-bottom: 1.5rem;">
        <a href="{{ route('admin.subjects.levels.index', $subject) }}" class="btn btn-secondary btn-sm" style="margin-bottom: 0.75rem;">
            &larr; Kembali ke Daftar Level
        </a>
        <h1 style="font-size: 1.8rem;">Tambah Tingkatan / Level Baru</h1>
        <p style="color: var(--text-muted); font-size: 0.9rem;">
            Mata Pelajaran: <strong>{{ $subject->name }}</strong>
        </p>
    </div>

    <div class="card">
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul style="margin-left: 1.25rem;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.subjects.levels.store', $subject) }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="name">Nama Level / Tingkatan *</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required placeholder="Contoh: Level 5: Expert / Master">
                </div>

                <div class="grid grid-cols-2">
                    <div class="form-group">
                        <label class="form-label" for="order">Urutan Level *</label>
                        <input type="number" name="order" id="order" class="form-control" value="{{ old('order', $nextOrder) }}" min="1" required>
                        <span class="form-text">Urutan hierarki bertahap siswa (1, 2, 3...).</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="required_points">Poin Syarat Naik Level *</label>
                        <input type="number" name="required_points" id="required_points" class="form-control" value="{{ old('required_points', 100) }}" min="10" required>
                        <span class="form-text">Standar: 100 poin (10 soal benar).</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="description">Deskripsi Materi & Target Level</label>
                    <textarea name="description" id="description" class="form-control" placeholder="Jelaskan ringkas apa yang dipelajari pada level ini...">{{ old('description') }}</textarea>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary">Simpan Level</button>
                    <a href="{{ route('admin.subjects.levels.index', $subject) }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
