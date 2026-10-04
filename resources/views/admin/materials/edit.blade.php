@extends('layouts.app')

@section('title', 'Edit Materi Slide - Musashi Learning')

@section('content')
<div style="max-width: 760px; margin: 0 auto;">
    <div style="margin-bottom: 1.5rem;">
        <a href="{{ route('admin.materials.index') }}" class="btn btn-secondary btn-sm" style="margin-bottom: 0.75rem;">
            &larr; Kembali ke Daftar Materi
        </a>
        <h1 style="font-size: 1.8rem; font-weight: 800;">Edit Materi: {{ $material->title }}</h1>
        <p style="color: var(--text-muted); font-size: 0.9rem;">
            Perbarui kelas tujuan, judul, deskripsi, uraian materi, atau file presentasi.
        </p>
    </div>

    <div class="card" style="box-shadow: var(--shadow-sm);">
        <div class="card-body" style="padding: 2rem;">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul style="margin-left: 1.25rem;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.materials.update', $material) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 1.25rem;">
                    @php
                        $maxLvlCount = $subjects->first()?->levels?->count() ?? 25;
                        $meetingLabel = $isTeacher ? "Pilihan Pertemuan Belajar (Pertemuan 1 - {$maxLvlCount}) *" : "Tingkatan Level / Pertemuan *";
                    @endphp
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" for="level_id" style="font-weight: 700;">
                            {{ $meetingLabel }}
                        </label>
                        <select name="level_id" id="level_id" class="form-control" required style="font-weight: 600;">
                            @foreach($subjects as $subj)
                                <optgroup label="{{ $subj->name }}">
                                    @foreach($subj->levels as $lvl)
                                        <option value="{{ $lvl->id }}" {{ old('level_id', $material->level_id) == $lvl->id ? 'selected' : '' }}>
                                            🗓️ {{ $lvl->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>

                    @php
                        $currentUser = Auth::user();
                        $isJpTeacher = ($isTeacher ?? false) && ($currentUser && $currentUser->subject_id == 2);
                        $isEnTeacher = ($isTeacher ?? false) && ($currentUser && $currentUser->subject_id == 1);
                        
                        $classesI = isset($classes) ? $classes->filter(fn($c) => str_starts_with(strtoupper($c->name), 'I')) : collect();
                        $classesB = isset($classes) ? $classes->filter(fn($c) => str_starts_with(strtoupper($c->name), 'B')) : collect();
                        $classesE = isset($classes) ? $classes->filter(fn($c) => str_starts_with(strtoupper($c->name), 'E')) : collect();
                        $hasLetterGroups = $isEnTeacher || ($classesI->isNotEmpty() || $classesB->isNotEmpty() || $classesE->isNotEmpty());
                        $selectedVal = old('class_name', $material->class_name);
                    @endphp
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" for="class_name" style="font-weight: 700;">
                            {{ $isJpTeacher ? 'Khusus Grup (Spesifik)' : 'Khusus Kelas / Grup Huruf' }}
                        </label>
                        <select name="class_name" id="class_name" class="form-control" style="font-weight: 600;">
                            <option value="">-- 🌐 Berlaku untuk Semua {{ $isJpTeacher ? 'Grup' : 'Kelas' }} --</option>
                            
                            @if($hasLetterGroups)
                                <optgroup label="── GABUNGAN GRUP HURUF (1x Upload untuk Seluruh Kelas Sehuruf) ──">
                                    <option value="I" {{ $selectedVal === 'I' ? 'selected' : '' }}>
                                        📚 Seluruh Kelas I (I1, I2, I3, I4, I5, I6)
                                    </option>
                                    <option value="B" {{ $selectedVal === 'B' ? 'selected' : '' }}>
                                        📚 Seluruh Kelas B (B1, B2, B3, B4, B5, B6, B7, B8)
                                    </option>
                                    <option value="E" {{ $selectedVal === 'E' ? 'selected' : '' }}>
                                        📚 Seluruh Kelas E (E1)
                                    </option>
                                </optgroup>
                                
                                <optgroup label="── PILIHAN PER KELAS TUNGGAL / SPESIFIK ──">
                                    @if(isset($classes))
                                        @foreach($classes as $cls)
                                            <option value="{{ $cls->name }}" {{ $selectedVal === $cls->name ? 'selected' : '' }}>
                                                Kelas {{ $cls->name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </optgroup>
                            @else
                                @if(isset($classes))
                                    @foreach($classes as $cls)
                                        <option value="{{ $cls->name }}" {{ $selectedVal === $cls->name ? 'selected' : '' }}>
                                            {{ $cls->name }}
                                        </option>
                                    @endforeach
                                @endif
                            @endif
                        </select>

                        @if($hasLetterGroups)
                            <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-top: 6px;">
                                <span style="font-size: 0.72rem; color: #64748b; font-weight: 700; align-self: center;">Pilih Cepat:</span>
                                <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('class_name').value = '';" style="font-size: 0.74rem; padding: 2px 8px;">
                                    🌐 Semua Kelas
                                </button>
                                <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('class_name').value = 'I';" style="font-size: 0.74rem; padding: 2px 8px; color: #4338ca; border-color: #c7d2fe; font-weight: 700;">
                                    📚 Grup I (I1-I6)
                                </button>
                                <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('class_name').value = 'B';" style="font-size: 0.74rem; padding: 2px 8px; color: #4338ca; border-color: #c7d2fe; font-weight: 700;">
                                    📚 Grup B (B1-B8)
                                </button>
                                <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('class_name').value = 'E';" style="font-size: 0.74rem; padding: 2px 8px; color: #4338ca; border-color: #c7d2fe; font-weight: 700;">
                                    📚 Grup E (E1)
                                </button>
                            </div>
                        @endif

                        <small style="font-size: 0.74rem; color: #64748b; display: block; margin-top: 4px;">
                            @if($hasLetterGroups)
                                Pilih grup huruf (misal <strong>Grup I</strong>) agar 1 kali upload materi otomatis dapat diakses oleh <strong>semua kelas sehuruf (I1, I2, I3, dst)</strong>.
                            @else
                                Jika dipilih, hanya siswa di {{ $isJpTeacher ? 'grup' : 'kelas' }} tersebut yang dapat melihat materi ini.
                            @endif
                        </small>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="title" style="font-weight: 700;">Judul Modul Materi *</label>
                    <input type="text" name="title" id="title" class="form-control" value="{{ old('title', $material->title) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="description" style="font-weight: 700;">Deskripsi Singkat Materi</label>
                    <textarea name="description" id="description" class="form-control" rows="2">{{ old('description', $material->description) }}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" for="content" style="font-weight: 700;">Isi / Catatan & Teks Penjelasan Materi (Dibaca Langsung oleh Siswa)</label>
                    <textarea name="content" id="content" class="form-control" rows="6" placeholder="Tuliskan materi tertulis, poin-poin penjelasan penting, atau instruksi belajar agar siswa dapat membaca materi ini langsung di Learning Musashi...">{{ old('content', $material->content) }}</textarea>
                    <span class="form-text">Opsional. Tulisan ini akan ditampilkan langsung di Learning Musashi baca materi siswa.</span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="slide_file" style="font-weight: 700;">File Lampiran Saat Ini</label>
                    @if($material->file_path)
                        <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 10px 14px; margin-bottom: 10px; display: flex; align-items: center; justify-content: space-between;">
                            <div>
                                <span style="font-weight: 700; color: #0f172a;">{{ $material->file_name }}</span>
                                <span class="badge badge-neutral" style="margin-left: 8px;">{{ strtoupper($material->file_type) }}</span>
                            </div>
                            <a href="{{ route('admin.materials.download', $material) }}" class="btn btn-secondary btn-sm">Unduh File</a>
                        </div>
                    @endif
                    <label class="form-label" for="slide_file" style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">Ganti File (Kosongkan jika tidak ingin mengubah):</label>
                    <input type="file" name="slide_file" id="slide_file" class="form-control" accept=".ppt,.pptx,.pdf,.pps,.ppsx,.doc,.docx,.txt,.png,.jpg,.jpeg,.webp">
                </div>

                <div class="form-group">
                    <label class="form-label" for="slide_url" style="font-weight: 700;">Tautan Slide Eksternal (Opsional)</label>
                    <input type="url" name="slide_url" id="slide_url" class="form-control" value="{{ old('slide_url', $material->slide_url) }}" placeholder="https://docs.google.com/presentation/d/.../embed">
                </div>

                <div class="form-group">
                    <label class="form-label" for="order" style="font-weight: 700;">Nomor Urutan Tampil *</label>
                    <input type="number" name="order" id="order" class="form-control" value="{{ old('order', $material->order) }}" min="1" required style="width: 120px;">
                </div>

                <div class="form-group" style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 14px 16px;">
                    <label class="form-label" style="font-weight: 800; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                        <span>Status Akses Materi Siswa</span>
                    </label>
                    <input type="hidden" name="is_active" value="0">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; margin-bottom: 0;">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $material->is_active ? '1' : '0') == '1' ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: var(--primary);">
                        <span style="font-weight: 600; font-size: 0.95rem; color: #0f172a;">
                            Aktifkan materi ini (Dapat langsung dilihat & diakses oleh siswa)
                        </span>
                    </label>
                    <small style="color: var(--text-muted); font-size: 0.8rem; display: block; margin-top: 6px; margin-left: 28px;">
                        Jika dimatikan (tidak dicentang), materi akan disembunyikan dari daftar materi dan dashboard siswa sampai Anda mengaktifkannya kembali.
                    </small>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary" style="font-weight: 700; padding: 10px 22px;">Simpan Perubahan Materi</button>
                    <a href="{{ route('admin.materials.index') }}" class="btn btn-secondary" style="padding: 10px 18px;">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
