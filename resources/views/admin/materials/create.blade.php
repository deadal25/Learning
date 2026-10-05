@extends('layouts.app')

@section('title', 'Upload Materi Slide PPT - Musashi Learning')

@section('content')
<div style="max-width: 760px; margin: 0 auto;">
    <div style="margin-bottom: 1.5rem;">
        <a href="{{ route('admin.materials.index') }}" class="btn btn-secondary btn-sm" style="margin-bottom: 0.75rem;">
            &larr; Kembali ke Daftar Materi
        </a>
        <h1 style="font-size: 1.8rem; font-weight: 800;">Upload Materi Presentasi / Slide PPT</h1>
        <p style="color: var(--text-muted); font-size: 0.9rem;">
            Tentukan kelas dan tingkatan level materi, lalu lampirkan file presentasi PPT, PPTX, PDF, atau link embed.
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

            <form action="{{ route('admin.materials.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 1.25rem;">
                    @php
                        $maxLvlCount = $subjects->first()?->levels?->count() ?? 25;
                        $meetingLabel = $isTeacher ? "Pilihan Pertemuan Belajar (Pertemuan 1 - {$maxLvlCount}) *" : "Tingkatan Level / Pertemuan *";
                        $placeholderLabel = $isTeacher ? "-- Pilih Pertemuan Belajar (Pertemuan 1 - {$maxLvlCount}) --" : "-- Pilih Level / Pertemuan --";
                    @endphp
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" for="level_id" style="font-weight: 700;">
                            {{ $meetingLabel }}
                        </label>
                        <select name="level_id" id="level_id" class="form-control" required style="font-weight: 600;">
                            <option value="">{{ $placeholderLabel }}</option>
                            @foreach($subjects as $subj)
                                <optgroup label="{{ $subj->name }}">
                                    @foreach($subj->levels as $lvl)
                                        <option value="{{ $lvl->id }}" {{ old('level_id') == $lvl->id ? 'selected' : '' }}>
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
                    @endphp
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" for="class_name" style="font-weight: 700;">
                            {{ $isJpTeacher ? 'Khusus Grup (Spesifik)' : 'Khusus Kelas / Grup Huruf' }}
                        </label>
                        <select name="class_name" id="class_name" class="form-control" style="font-weight: 600;">
                            <option value="">-- 🌐 Berlaku untuk Semua {{ $isJpTeacher ? 'Grup' : 'Kelas' }} --</option>
                            
                            @if($hasLetterGroups)
                                <optgroup label="── GABUNGAN GRUP HURUF (1x Upload untuk Seluruh Kelas Sehuruf) ──">
                                    <option value="I" {{ old('class_name') === 'I' ? 'selected' : '' }}>
                                        📚 Seluruh Kelas I (I1, I2, I3, I4, I5, I6)
                                    </option>
                                    <option value="B" {{ old('class_name') === 'B' ? 'selected' : '' }}>
                                        📚 Seluruh Kelas B (B1, B2, B3, B4, B5, B6, B7, B8)
                                    </option>
                                    <option value="E" {{ old('class_name') === 'E' ? 'selected' : '' }}>
                                        📚 Seluruh Kelas E (E1)
                                    </option>
                                </optgroup>
                                
                                <optgroup label="── PILIHAN PER KELAS TUNGGAL / SPESIFIK ──">
                                    @if(isset($classes))
                                        @foreach($classes as $cls)
                                            <option value="{{ $cls->name }}" {{ old('class_name') === $cls->name ? 'selected' : '' }}>
                                                Kelas {{ $cls->name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </optgroup>
                            @else
                                @if(isset($classes))
                                    @foreach($classes as $cls)
                                        <option value="{{ $cls->name }}" {{ old('class_name') === $cls->name ? 'selected' : '' }}>
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
                    <input type="text" name="title" id="title" class="form-control" value="{{ old('title') }}" required placeholder="Contoh: Meeting 1: Self-Introduction & Greetings">
                </div>

                <div class="form-group">
                    <label class="form-label" for="description" style="font-weight: 700;">Deskripsi Singkat Materi</label>
                    <textarea name="description" id="description" class="form-control" rows="2" placeholder="Tuliskan gambaran ringkas modul slide ini...">{{ old('description') }}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" for="content" style="font-weight: 700;">Isi / Catatan & Teks Penjelasan Materi (Dibaca Langsung oleh Siswa)</label>
                    <textarea name="content" id="content" class="form-control" rows="6" placeholder="Tuliskan materi tertulis, poin-poin penjelasan penting, atau instruksi belajar agar siswa dapat membaca materi ini langsung di Learning Musashi tanpa harus membuka aplikasi lain...">{{ old('content') }}</textarea>
                    <span class="form-text">Opsional. Tulisan ini akan ditampilkan langsung di Learning Musashi baca materi siswa.</span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="slide_file" style="font-weight: 700;">File Presentasi PPT, PPTX, PDF, DOC, atau Gambar</label>
                    <input type="file" name="slide_file" id="slide_file" class="form-control" accept=".ppt,.pptx,.pdf,.pps,.ppsx,.doc,.docx,.txt,.png,.jpg,.jpeg,.webp">
                    <div id="fileSizeStatusBox" style="display: none; margin-top: 8px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem; border: 1px solid #cbd5e1;"></div>
                    <div id="chunkProgressWrap" style="display: none; margin-top: 10px;">
                        <div style="height: 8px; width: 100%; background: #e2e8f0; border-radius: 9999px; overflow: hidden;">
                            <div id="chunkProgressBar" style="height: 100%; width: 0%; background: #4f46e5; transition: width 0.2s;"></div>
                        </div>
                        <div id="chunkProgressText" style="font-size: 0.8rem; color: #4338ca; margin-top: 4px; font-weight: 600;"></div>
                    </div>
                    <span class="form-text" style="margin-top: 6px;">
                        Format yang didukung: <strong>.ppt, .pptx, .pdf, .docx, .png, .jpg</strong>.
                        Untuk berkas besar (> 4 MB), sistem akan otomatis mengunggah dalam pecahan data aman untuk menghindari limit serverless Vercel.
                    </span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="slide_url" style="font-weight: 700;">Tautan Slide Eksternal (Opsional)</label>
                    <input type="url" name="slide_url" id="slide_url" class="form-control" value="{{ old('slide_url') }}" placeholder="https://docs.google.com/presentation/d/.../embed atau link Google Drive / Canva">
                    <span class="form-text">Tautan Google Slides / Canva / Google Drive / OneDrive jika menggunakan presentasi daring.</span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="order" style="font-weight: 700;">Nomor Urutan Tampil *</label>
                    <input type="number" name="order" id="order" class="form-control" value="{{ old('order', 1) }}" min="1" required style="width: 120px;">
                </div>

                <div class="form-group" style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 14px 16px;">
                    <label class="form-label" style="font-weight: 800; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                        <span>Status Akses Materi Siswa</span>
                    </label>
                    <input type="hidden" name="is_active" value="0">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; margin-bottom: 0;">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: var(--primary);">
                        <span style="font-weight: 600; font-size: 0.95rem; color: #0f172a;">
                            Aktifkan materi ini sekarang (Dapat langsung dilihat & diakses oleh siswa)
                        </span>
                    </label>
                    <small style="color: var(--text-muted); font-size: 0.8rem; display: block; margin-top: 6px; margin-left: 28px;">
                        Jika tidak dicentang (nonaktif), materi akan disimpan namun tersembunyi dari akun/portal siswa sampai Anda mengaktifkannya kembali.
                    </small>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 2rem;">
                    <button type="submit" id="btnSubmitForm" class="btn btn-primary" style="font-weight: 700; padding: 10px 22px;">Upload & Simpan Materi</button>
                    <a href="{{ route('admin.materials.index') }}" class="btn btn-secondary" style="padding: 10px 18px;">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form[action="{{ route('admin.materials.store') }}"]');
    const fileInput = document.getElementById('slide_file');
    const statusBox = document.getElementById('fileSizeStatusBox');
    const progressWrap = document.getElementById('chunkProgressWrap');
    const progressBar = document.getElementById('chunkProgressBar');
    const progressText = document.getElementById('chunkProgressText');
    const submitBtn = document.getElementById('btnSubmitForm');

    if (!form || !fileInput) return;

    fileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (!file) {
            statusBox.style.display = 'none';
            return;
        }

        const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
        statusBox.style.display = 'block';

        if (file.size > 3.5 * 1024 * 1024) {
            statusBox.innerHTML = '⚡ <strong>File berukuran ' + sizeMB + ' MB.</strong> Sistem akan otomatis membagi berkas menjadi pecahan aman agar tidak terkena limit 4.5 MB Vercel saat Anda menekan tombol Simpan.';
            statusBox.style.background = '#eef2ff';
            statusBox.style.color = '#3730a3';
            statusBox.style.borderColor = '#c7d2fe';
        } else {
            statusBox.innerHTML = '✅ <strong>File berukuran ' + sizeMB + ' MB.</strong> Ukuran file aman untuk diunggah langsung.';
            statusBox.style.background = '#ecfdf5';
            statusBox.style.color = '#065f46';
            statusBox.style.borderColor = '#a7f3d0';
        }
    });

    form.addEventListener('submit', async function(e) {
        const file = fileInput.files[0];
        const preuploaded = form.querySelector('input[name="preuploaded_file_path"]');

        if (!file || file.size <= 3.5 * 1024 * 1024 || (preuploaded && preuploaded.value)) {
            return true;
        }

        e.preventDefault();

        const CHUNK_SIZE = 2 * 1024 * 1024; // 2 MB
        const totalChunks = Math.ceil(file.size / CHUNK_SIZE);
        const uploadId = 'up_' + Date.now() + '_' + Math.random().toString(36).substring(2, 8);

        progressWrap.style.display = 'block';
        submitBtn.disabled = true;
        const origBtnText = submitBtn.innerText;
        submitBtn.innerText = 'Mengunggah Berkas...';

        const csrfToken = form.querySelector('input[name="_token"]')?.value;

        try {
            for (let i = 0; i < totalChunks; i++) {
                const start = i * CHUNK_SIZE;
                const end = Math.min(file.size, start + CHUNK_SIZE);
                const chunkBlob = file.slice(start, end);

                const formData = new FormData();
                formData.append('_token', csrfToken);
                formData.append('upload_id', uploadId);
                formData.append('chunk_index', i);
                formData.append('total_chunks', totalChunks);
                formData.append('file_name', file.name);
                formData.append('chunk_data', chunkBlob, file.name);

                const percent = Math.round(((i) / totalChunks) * 100);
                progressBar.style.width = percent + '%';
                progressText.innerText = 'Mengunggah potongan aman ' + (i + 1) + ' dari ' + totalChunks + ' (' + percent + '%)...';

                const response = await fetch("{{ route('admin.materials.chunk-upload') }}", {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error('Server mengembalikan status ' + response.status + ' saat mengunggah bagian ' + (i + 1));
                }

                const result = await response.json();
                if (result.completed) {
                    progressBar.style.width = '100%';
                    progressText.innerText = '✅ Berkas utuh selesai diproses di server. Menyimpan data materi...';

                    let inputPath = form.querySelector('input[name="preuploaded_file_path"]');
                    if (!inputPath) {
                        inputPath = document.createElement('input');
                        inputPath.type = 'hidden';
                        inputPath.name = 'preuploaded_file_path';
                        form.appendChild(inputPath);
                    }
                    inputPath.value = result.file_path;

                    let inputName = form.querySelector('input[name="preuploaded_file_name"]');
                    if (!inputName) {
                        inputName = document.createElement('input');
                        inputName.type = 'hidden';
                        inputName.name = 'preuploaded_file_name';
                        form.appendChild(inputName);
                    }
                    inputName.value = result.file_name;

                    fileInput.value = '';
                    form.submit();
                    return;
                }
            }
        } catch (err) {
            console.error('Upload chunk error:', err);
            progressText.innerHTML = '<span style="color: #ef4444; font-weight: 700;">❌ ' + err.message + '</span><br><small style="color: #64748b;">Gagal mengunggah berkas. Anda dapat menggunakan Tautan Slide Google Drive/Canva di bawah sebagai alternatif.</small>';
            submitBtn.disabled = false;
            submitBtn.innerText = origBtnText;
        }
    });
});
</script>
@endpush
@endsection
