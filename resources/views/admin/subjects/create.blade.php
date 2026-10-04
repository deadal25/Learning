@extends('layouts.app')

@section('title', 'Tambah Mata Pelajaran Baru - Musashi Learning')

@section('content')
<div style="max-width: 680px; margin: 0 auto;">
    <div style="margin-bottom: 1.5rem;">
        <a href="{{ route('admin.subjects.index') }}" class="btn btn-secondary btn-sm" style="margin-bottom: 0.75rem;">
            &larr; Kembali ke Daftar Mapel
        </a>
        <h1 style="font-size: 1.8rem;">Tambah Mata Pelajaran Baru</h1>
        <p style="color: var(--text-muted); font-size: 0.9rem;">
            Tambahkan mata pelajaran baru ke kurikulum Musashi, kemudian atur level bertingkatnya.
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

            <form action="{{ route('admin.subjects.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="name">Nama Mata Pelajaran *</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required placeholder="Contoh: Bahasa Jerman">
                </div>

                <div class="form-group">
                    <label class="form-label" for="description">Deskripsi Singkat</label>
                    <textarea name="description" id="description" class="form-control" placeholder="Jelaskan ringkas silabus atau tujuan pembelajaran...">{{ old('description') }}</textarea>
                </div>

                <div class="grid grid-cols-2">
                    <div class="form-group">
                        <label class="form-label" for="badge_color">Warna Tema Lencana</label>
                        <input type="color" name="badge_color" id="badge_color" class="form-control" style="height: 48px; padding: 4px;" value="{{ old('badge_color', '#4f46e5') }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="order">Urutan Tampilan</label>
                        <input type="number" name="order" id="order" class="form-control" value="{{ old('order', 1) }}" min="1">
                    </div>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary">Simpan Mata Pelajaran</button>
                    <a href="{{ route('admin.subjects.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
