@extends('layouts.app')

@section('title', 'Edit Level: ' . $level->name)

@section('content')
<div style="max-width: 680px; margin: 0 auto;">
    <div style="margin-bottom: 1.5rem;">
        <a href="{{ route('admin.subjects.levels.index', $subject) }}" class="btn btn-secondary btn-sm" style="margin-bottom: 0.75rem;">
            &larr; Kembali ke Daftar Level
        </a>
        <h1 style="font-size: 1.8rem;">Edit Tingkatan / Level: {{ $level->name }}</h1>
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

            <form action="{{ route('admin.subjects.levels.update', [$subject, $level]) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="form-label" for="name">Nama Level / Tingkatan *</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $level->name) }}" required>
                </div>

                <div class="grid grid-cols-2">
                    <div class="form-group">
                        <label class="form-label" for="order">Urutan Level *</label>
                        <input type="number" name="order" id="order" class="form-control" value="{{ old('order', $level->order) }}" min="1" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="required_points">Poin Syarat Naik Level *</label>
                        <input type="number" name="required_points" id="required_points" class="form-control" value="{{ old('required_points', $level->required_points) }}" min="10" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="description">Deskripsi Materi & Target Level</label>
                    <textarea name="description" id="description" class="form-control">{{ old('description', $level->description) }}</textarea>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="{{ route('admin.subjects.levels.index', $subject) }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
