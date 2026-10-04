@extends('layouts.app')

@section('title', 'Edit Soal Latihan - Musashi Learning')

@section('content')
<div style="max-width: 760px; margin: 0 auto;">
    <div style="margin-bottom: 1.5rem;">
        <a href="{{ route('admin.exercises.index', ['level_id' => $exercise->level_id]) }}" class="btn btn-secondary btn-sm" style="margin-bottom: 0.75rem;">
            &larr; Kembali ke Bank Soal
        </a>
        <h1 style="font-size: 1.8rem;">Edit Soal Latihan #{{ $exercise->question_number }}</h1>
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

            <form action="{{ route('admin.exercises.update', $exercise) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-2">
                    <div class="form-group">
                        <label class="form-label" for="level_id">Mata Pelajaran & Level *</label>
                        <select name="level_id" id="level_id" class="form-control" required>
                            @foreach($subjects as $subj)
                                <optgroup label="{{ $subj->name }}">
                                    @foreach($subj->levels as $lvl)
                                        <option value="{{ $lvl->id }}" {{ old('level_id', $exercise->level_id) == $lvl->id ? 'selected' : '' }}>
                                            {{ $subj->name }} &rsaquo; {{ $lvl->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="question_number">Nomor Urut Soal *</label>
                        <input type="number" name="question_number" id="question_number" class="form-control" value="{{ old('question_number', $exercise->question_number) }}" min="1" max="20" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="question">Pertanyaan / Soal *</label>
                    <textarea name="question" id="question" class="form-control" rows="3" required>{{ old('question', $exercise->question) }}</textarea>
                </div>

                <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1.25rem; margin-bottom: 1.25rem;">
                    <div style="font-weight: 700; color: #334155; margin-bottom: 12px; font-size: 0.92rem;">
                        Pilihan Jawaban (A, B, C, D):
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="option_a">Pilihan Jawaban A *</label>
                        <input type="text" name="option_a" id="option_a" class="form-control" value="{{ old('option_a', $exercise->option_a) }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="option_b">Pilihan Jawaban B *</label>
                        <input type="text" name="option_b" id="option_b" class="form-control" value="{{ old('option_b', $exercise->option_b) }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="option_c">Pilihan Jawaban C *</label>
                        <input type="text" name="option_c" id="option_c" class="form-control" value="{{ old('option_c', $exercise->option_c) }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="option_d">Pilihan Jawaban D *</label>
                        <input type="text" name="option_d" id="option_d" class="form-control" value="{{ old('option_d', $exercise->option_d) }}" required>
                    </div>
                </div>

                <div class="grid grid-cols-2">
                    <div class="form-group">
                        <label class="form-label" for="correct_option">Kunci Jawaban Benar *</label>
                        <select name="correct_option" id="correct_option" class="form-control" required style="font-weight: 700; color: #059669;">
                            <option value="a" {{ old('correct_option', $exercise->correct_option) === 'a' ? 'selected' : '' }}>Opsi A</option>
                            <option value="b" {{ old('correct_option', $exercise->correct_option) === 'b' ? 'selected' : '' }}>Opsi B</option>
                            <option value="c" {{ old('correct_option', $exercise->correct_option) === 'c' ? 'selected' : '' }}>Opsi C</option>
                            <option value="d" {{ old('correct_option', $exercise->correct_option) === 'd' ? 'selected' : '' }}>Opsi D</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="points">Bobot Poin *</label>
                        <input type="number" name="points" id="points" class="form-control" value="{{ old('points', $exercise->points) }}" min="1" max="50" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="explanation">Penjelasan / Pembahasan Soal</label>
                    <textarea name="explanation" id="explanation" class="form-control">{{ old('explanation', $exercise->explanation) }}</textarea>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="{{ route('admin.exercises.index', ['level_id' => $exercise->level_id]) }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
