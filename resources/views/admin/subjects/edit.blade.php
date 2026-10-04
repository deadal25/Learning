@extends('layouts.app')

@section('title', 'Edit Mata Pelajaran - Musashi Learning')

@section('content')
<div style="max-width: 680px; margin: 0 auto;">
    <div style="margin-bottom: 1.5rem;">
        <a href="{{ route('admin.subjects.index') }}" class="btn btn-secondary btn-sm" style="margin-bottom: 0.75rem;">
            &larr; Kembali ke Daftar Mapel
        </a>
        <h1 style="font-size: 1.8rem;">Edit Mata Pelajaran: {{ $subject->name }}</h1>
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

            <form action="{{ route('admin.subjects.update', $subject) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="form-label" for="name">Nama Mata Pelajaran *</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $subject->name) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="description">Deskripsi Singkat</label>
                    <textarea name="description" id="description" class="form-control">{{ old('description', $subject->description) }}</textarea>
                </div>

                <div class="grid grid-cols-2">
                    <div class="form-group">
                        <label class="form-label" for="badge_color">Warna Tema Lencana</label>
                        <input type="color" name="badge_color" id="badge_color" class="form-control" style="height: 48px; padding: 4px;" value="{{ old('badge_color', $subject->badge_color) }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="order">Urutan Tampilan</label>
                        <input type="number" name="order" id="order" class="form-control" value="{{ old('order', $subject->order) }}" min="1">
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem;">
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        <a href="{{ route('admin.subjects.index') }}" class="btn btn-secondary">Batal</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
