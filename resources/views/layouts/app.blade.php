<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
    <meta name="description" content="Musashi - Platform Pembelajaran Bertingkat Berbasis Level dan Poin (Bahasa Inggris, Bahasa Jepang)">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    @php
        $defaultSiteTitle = 'Learning Musashi';
        if (auth()->check() && auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin()) {
            if (auth()->user()->subject_id == 2) {
                $defaultSiteTitle = 'Learning Musashi Guru Bahasa Jepang';
            } elseif (auth()->user()->subject_id == 3) {
                $defaultSiteTitle = 'Learning Musashi Guru Matematika';
            } else {
                $defaultSiteTitle = 'Learning Musashi Guru Bahasa Inggris';
            }
        } elseif (auth()->check() && auth()->user()->isStudent()) {
            $studentSubjId = session('active_subject_id') ?? auth()->user()->subject_id ?? 1;
            if ($studentSubjId == 2) {
                $defaultSiteTitle = 'Learning Musashi Siswa Bahasa Jepang';
            } elseif ($studentSubjId == 3) {
                $defaultSiteTitle = 'Learning Musashi Siswa Matematika';
            } else {
                $defaultSiteTitle = 'Learning Musashi Siswa Bahasa Inggris';
            }
        }
    @endphp
    <title>@yield('title', $defaultSiteTitle)</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/bola.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/bola.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Light Theme Only -->
    <script>
        try {
            localStorage.removeItem('musashi_theme');
        } catch (e) {}
    </script>

    <!-- Custom CSS Design System -->
    <link rel="stylesheet" href="{{ asset('css/musashi.css') }}">
    <style>
        .dropdown-item-link:hover {
            background-color: #f1f5f9;
        }
        #userDropdownToggle:hover {
            border-color: #cbd5e1 !important;
            background-color: #f8fafc !important;
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Top Navigation Bar -->
    <header class="navbar">
        <div class="container navbar-inner">
            <div style="display: flex; align-items: center; gap: 12px;">
                <a href="{{ auth()->check() ? route('dashboard') : url('/') }}" class="brand-logo" style="display: flex; align-items: center; gap: 10px; text-decoration: none;">
                    <img src="{{ asset('images/bola.png') }}" alt="Logo" style="height: 38px; width: auto; max-height: 38px; object-fit: contain; display: block;">
                    <img src="{{ asset('images/Gambar1.png') }}" alt="MUSASHI" style="height: 28px; width: auto; max-height: 28px; object-fit: contain; display: block;">
                </a>
                @if(auth()->check() && auth()->user()->isStudent())
                    @php
                        $activeSubjectId = session('active_subject_id') ?? auth()->user()->subject_id ?? 1;
                        $subjBadge = match((int)$activeSubjectId) {
                            2 => ['label' => 'Bahasa Jepang', 'bg' => '#fce7f3', 'color' => '#9d174d'],
                            3 => ['label' => 'Matematika', 'bg' => '#dcfce7', 'color' => '#166534'],
                            default => ['label' => 'Bahasa Inggris', 'bg' => '#dbeafe', 'color' => '#1e40af'],
                        };
                    @endphp
                    <span class="badge" style="background: {{ $subjBadge['bg'] }}; color: {{ $subjBadge['color'] }}; font-weight: 800; font-size: 0.78rem; padding: 4px 10px; border-radius: 9999px;">
                        {{ $subjBadge['label'] }}
                    </span>
                @endif
            </div>

            @auth
                <nav class="desktop-nav">
                    <ul class="nav-menu">
                        @if(auth()->user()->isSuperAdmin())
                            <li>
                                <a href="{{ route('superadmin.dashboard') }}" class="nav-link {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                                    Dashboard
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('superadmin.admins.index') }}" class="nav-link {{ request()->routeIs('superadmin.admins.*') ? 'active' : '' }}">
                                    Kelola Admin
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('superadmin.students.index') }}" class="nav-link {{ request()->routeIs('superadmin.students.*') ? 'active' : '' }}">
                                    Kelola Siswa
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('superadmin.questions.index') }}" class="nav-link {{ request()->routeIs('superadmin.questions.*') ? 'active' : '' }}">
                                    Kelola Soal
                                </a>
                            </li>
                        @elseif(auth()->user()->isAdmin())
                            <li>
                                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                                    Dashboard
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.materials.index') }}" class="nav-link {{ request()->routeIs('admin.materials.*') ? 'active' : '' }}">
                                    Materi
                                </a>
                            </li>
                            @if(auth()->user()->subject_id == 1)
                                {{-- Guru Bahasa Inggris: Dashboard, Materi, Nilai/Feedback (tanpa Latihan Soal) --}}
                                <li>
                                    <a href="{{ route('admin.grades.index') }}" class="nav-link {{ request()->routeIs('admin.grades.*') ? 'active' : '' }}" style="font-weight: 700;">
                                        Nilai / Feedback
                                    </a>
                                </li>
                            @elseif(auth()->user()->subject_id == 2)
                                {{-- Guru Bahasa Jepang: Dashboard, Materi, Latihan Soal --}}
                                <li>
                                    <a href="{{ route('admin.japanese.tests.index') }}" class="nav-link {{ request()->routeIs('admin.japanese.tests.*') ? 'active' : '' }}" style="font-weight: 700;">
                                        Latihan Soal
                                    </a>
                                </li>
                            @else
                                {{-- Guru Matematika & Lainnya: Dashboard, Materi, Latihan Soal --}}
                                <li>
                                    <a href="{{ route('admin.exercises.index') }}" class="nav-link {{ request()->routeIs('admin.exercises.*') ? 'active' : '' }}" style="font-weight: 700;">
                                        Latihan Soal
                                    </a>
                                </li>
                            @endif
                        @else
                            {{-- Student Navigation: Only 3 items (Beranda, Materi, Latihan Soal) for Japanese, English, and Math --}}
                            <li>
                                <a href="{{ route('student.dashboard') }}" class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
                                    Beranda
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('student.materials.index') }}" class="nav-link {{ request()->routeIs('student.materials.*') ? 'active' : '' }}">
                                    Materi
                                </a>
                            </li>
                            <li>
                                <a href="{{ auth()->user()->subject_id == 2 ? route('student.japanese.tests.index') : route('student.exercises.index') }}" class="nav-link {{ request()->routeIs('student.exercises.*') || request()->routeIs('student.japanese.tests.*') ? 'active' : '' }}">
                                    Latihan Soal
                                </a>
                            </li>
                        @endif
                    </ul>
                </nav>

                <div style="display: flex; align-items: center; gap: 0.75rem;">

                    @if(auth()->user()->isStudent())
                        <!-- Student Clickable Profile Pill with Dropdown Menu -->
                        <div class="user-dropdown-container" style="position: relative;">
                            <button type="button" id="userDropdownToggle" class="user-profile-badge" style="display: flex; align-items: center; gap: 10px; cursor: pointer; border: 1px solid var(--border-color); background: #ffffff; padding: 5px 14px 5px 8px; border-radius: 9999px; transition: all 0.2s ease; text-align: left;" onclick="toggleUserDropdown(event)" title="Buka Profil & Menu Siswa">
                                <div class="avatar" style="width: 34px; height: 34px; font-size: 0.9rem; font-weight: 800; background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                <div style="display: flex; flex-direction: column;">
                                    <div style="font-size: 0.9rem; font-weight: 700; color: #0f172a; line-height: 1.2;">
                                        {{ auth()->user()->name }}
                                    </div>
                                    <div style="font-size: 0.72rem; color: #64748b; font-weight: 600;">
                                        {{ auth()->user()->class_name ?: 'Siswa' }}
                                    </div>
                                </div>
                                <svg id="userDropdownChevron" style="width: 14px; height: 14px; color: #64748b; margin-left: 2px; transition: transform 0.2s;" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>

                            <!-- Dropdown Menu Box -->
                            <div id="userDropdownMenu" style="display: none; position: absolute; right: 0; top: calc(100% + 8px); width: 280px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: var(--radius-lg); box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.12), 0 8px 10px -6px rgba(0, 0, 0, 0.08); z-index: 1000; overflow: hidden;">
                                <!-- Header Info -->
                                <div style="padding: 14px 16px; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                    <div style="font-size: 0.74rem; text-transform: uppercase; font-weight: 800; color: #64748b; letter-spacing: 0.5px;">Akun Siswa Terdaftar</div>
                                    <div style="font-size: 0.95rem; font-weight: 800; color: #0f172a; margin-top: 2px;">{{ auth()->user()->name }}</div>
                                    <div style="font-size: 0.78rem; color: #475569; word-break: break-all;">{{ auth()->user()->email }}</div>
                                    <div style="display: flex; gap: 6px; margin-top: 6px; flex-wrap: wrap;">
                                        @if(auth()->user()->class_name)
                                            <span class="badge" style="background: #eff6ff; color: #1e40af; font-size: 0.72rem; font-weight: 700;">
                                                🎓 {{ auth()->user()->class_name }}
                                            </span>
                                        @endif
                                        @if(auth()->user()->division)
                                            <span class="badge" style="background: #f1f5f9; color: #334155; font-size: 0.72rem; font-weight: 600;">
                                                🏢 {{ auth()->user()->division }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Menu Items -->
                                <div style="padding: 6px 0;">
                                    <!-- 1. Profil Saya -->
                                    <a href="{{ route('student.profile.edit') }}" class="dropdown-item-link" style="display: flex; align-items: center; gap: 12px; padding: 10px 16px; color: #1e293b; font-size: 0.88rem; font-weight: 600; text-decoration: none; transition: background 0.15s;">
                                        <span style="font-size: 1.15rem; width: 22px; text-align: center;">👤</span>
                                        <div>
                                            <div>Profil Saya</div>
                                            <div style="font-size: 0.72rem; color: #64748b; font-weight: 400;">Lihat data, ganti email & password</div>
                                        </div>
                                    </a>

                                    <!-- 2. Riwayat Absensi -->
                                    <a href="{{ route('student.attendance.index') }}" class="dropdown-item-link" style="display: flex; align-items: center; gap: 12px; padding: 10px 16px; color: #1e293b; font-size: 0.88rem; font-weight: 600; text-decoration: none; transition: background 0.15s;">
                                        <span style="font-size: 1.15rem; width: 22px; text-align: center;">📅</span>
                                        <div>
                                            <div>Riwayat Absensi</div>
                                            <div style="font-size: 0.72rem; color: #64748b; font-weight: 400;">Presensi & riwayat jam hadir</div>
                                        </div>
                                    </a>



                                    <!-- 3. Riwayat Skor -->
                                    @if(auth()->user()->subject_id == 2)
                                        <a href="{{ route('student.japanese.grades.index') }}" class="dropdown-item-link" style="display: flex; align-items: center; gap: 12px; padding: 10px 16px; color: #1e293b; font-size: 0.88rem; font-weight: 600; text-decoration: none; transition: background 0.15s;">
                                            <span style="font-size: 1.15rem; width: 22px; text-align: center;">📊</span>
                                            <div>
                                                <div>Riwayat Skor</div>
                                                <div style="font-size: 0.72rem; color: #64748b; font-weight: 400;">Nilai tes evaluasi 4 pertemuan</div>
                                            </div>
                                        </a>
                                    @elseif(auth()->user()->subject_id == 1)
                                        <a href="{{ route('student.grades.index') }}" class="dropdown-item-link" style="display: flex; align-items: center; gap: 12px; padding: 10px 16px; color: #1e293b; font-size: 0.88rem; font-weight: 600; text-decoration: none; transition: background 0.15s;">
                                            <span style="font-size: 1.15rem; width: 22px; text-align: center;">📊</span>
                                            <div>
                                                <div>Riwayat Skor</div>
                                                <div style="font-size: 0.72rem; color: #64748b; font-weight: 400;">Skor perolehan latihan soal</div>
                                            </div>
                                        </a>
                                    @else
                                        <a href="{{ route('student.attendance.index') }}" class="dropdown-item-link" style="display: flex; align-items: center; gap: 12px; padding: 10px 16px; color: #1e293b; font-size: 0.88rem; font-weight: 600; text-decoration: none; transition: background 0.15s;">
                                            <span style="font-size: 1.15rem; width: 22px; text-align: center;">📊</span>
                                            <div>
                                                <div>Riwayat Skor / Poin</div>
                                                <div style="font-size: 0.72rem; color: #64748b; font-weight: 400;">Poin capaian pembelajaran</div>
                                            </div>
                                        </a>
                                    @endif
                                </div>

                                <!-- 4. Tombol Keluar (Inside dropdown) -->
                                <div style="border-top: 1px solid #e2e8f0; padding: 8px 12px; background: #f8fafc;">
                                    <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                                        @csrf
                                        <button type="submit" style="width: 100%; display: flex; align-items: center; gap: 10px; padding: 9px 12px; background: none; border: none; border-radius: var(--radius-sm); color: #dc2626; font-size: 0.88rem; font-weight: 700; cursor: pointer; text-align: left; transition: background 0.15s;" onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='none'">
                                            <span style="font-size: 1.1rem;">🚪</span>
                                            <span>Keluar dari Akun</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @elseif(auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin())
                        <!-- Teacher Clickable Profile Pill with Dropdown Menu -->
                        <div class="user-dropdown-container" style="position: relative;">
                            <button type="button" id="userDropdownToggle" class="user-profile-badge" style="display: flex; align-items: center; gap: 10px; cursor: pointer; border: 1px solid var(--border-color); background: #ffffff; padding: 5px 14px 5px 8px; border-radius: 9999px; transition: all 0.2s ease; text-align: left;" onclick="toggleUserDropdown(event)" title="Buka Menu Pengajar">
                                <div class="avatar" style="width: 34px; height: 34px; font-size: 0.9rem; font-weight: 800; background: linear-gradient(135deg, {{ auth()->user()->subject_id == 2 ? '#db2777, #be185d' : (auth()->user()->subject_id == 3 ? '#059669, #047857' : '#2563eb, #1d4ed8') }}); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                <div style="display: flex; flex-direction: column;">
                                    <div style="font-size: 0.9rem; font-weight: 700; color: #0f172a; line-height: 1.2;">
                                        {{ auth()->user()->name }}
                                    </div>
                                    <div style="font-size: 0.72rem; color: #64748b; font-weight: 600;">
                                        Pengajar
                                    </div>
                                </div>
                                <svg id="userDropdownChevron" style="width: 14px; height: 14px; color: #64748b; margin-left: 2px; transition: transform 0.2s;" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>

                            <!-- Dropdown Menu Box -->
                            <div id="userDropdownMenu" style="display: none; position: absolute; right: 0; top: calc(100% + 8px); width: 280px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: var(--radius-lg); box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.12), 0 8px 10px -6px rgba(0, 0, 0, 0.08); z-index: 1000; overflow: hidden;">
                                <!-- Header Info -->
                                <div style="padding: 14px 16px; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                    <div style="font-size: 0.74rem; text-transform: uppercase; font-weight: 800; color: #64748b; letter-spacing: 0.5px;">Akun Pengajar Terdaftar</div>
                                    <div style="font-size: 0.95rem; font-weight: 800; color: #0f172a; margin-top: 2px;">{{ auth()->user()->name }}</div>
                                    <div style="font-size: 0.78rem; color: #475569; word-break: break-all;">{{ auth()->user()->email }}</div>
                                    <div style="display: flex; gap: 6px; margin-top: 6px; flex-wrap: wrap;">
                                        @if(auth()->user()->subject_id == 2)
                                            <span class="badge" style="background: #fdf2f8; color: #be185d; font-size: 0.72rem; font-weight: 700;">
                                                Bahasa Jepang
                                            </span>
                                        @elseif(auth()->user()->subject_id == 1)
                                            <span class="badge" style="background: #eff6ff; color: #1e40af; font-size: 0.72rem; font-weight: 700;">
                                                Bahasa Inggris
                                            </span>
                                        @elseif(auth()->user()->subject_id == 3)
                                            <span class="badge" style="background: #ecfdf5; color: #065f46; font-size: 0.72rem; font-weight: 700;">
                                                Matematika
                                            </span>
                                        @endif
                                        <span class="badge" style="background: #f1f5f9; color: #334155; font-size: 0.72rem; font-weight: 600;">
                                            Pengajar
                                        </span>
                                    </div>
                                </div>

                                <!-- Menu Items -->
                                <div style="padding: 6px 0;">
                                    <!-- 1. Kelola Siswa -->
                                    <a href="{{ route('admin.students.index') }}" class="dropdown-item-link" style="display: flex; align-items: center; gap: 12px; padding: 10px 16px; color: #1e293b; font-size: 0.88rem; font-weight: 600; text-decoration: none; transition: background 0.15s;">
                                        <span style="font-size: 1.15rem; width: 22px; text-align: center;">👥</span>
                                        <div>
                                            <div>Kelola Siswa</div>
                                            <div style="font-size: 0.72rem; color: #64748b; font-weight: 400;">Data & akun siswa terdaftar</div>
                                        </div>
                                    </a>

                                    <!-- 2. Kelola Absensi / Riwayat Absensi -->
                                    <a href="{{ route('admin.attendance.index') }}" class="dropdown-item-link" style="display: flex; align-items: center; gap: 12px; padding: 10px 16px; color: #1e293b; font-size: 0.88rem; font-weight: 600; text-decoration: none; transition: background 0.15s;">
                                        <span style="font-size: 1.15rem; width: 22px; text-align: center;">⏱️</span>
                                        <div>
                                            <div>{{ auth()->user()->subject_id == 1 ? 'Riwayat & Kelola Absensi' : 'Kelola Absensi' }}</div>
                                            <div style="font-size: 0.72rem; color: #64748b; font-weight: 400;">Presensi & rekap jam hadir siswa</div>
                                        </div>
                                    </a>

                                    <!-- 3. Kelola Grup / Kelola Kelas -->
                                    <a href="{{ route('admin.classes.index') }}" class="dropdown-item-link" style="display: flex; align-items: center; gap: 12px; padding: 10px 16px; color: #1e293b; font-size: 0.88rem; font-weight: 600; text-decoration: none; transition: background 0.15s;">
                                        <span style="font-size: 1.15rem; width: 22px; text-align: center;">🏫</span>
                                        <div>
                                            <div>{{ auth()->user()->subject_id == 2 ? 'Kelola Grup' : (auth()->user()->subject_id == 3 ? 'Kelola Kelas Matematika' : 'Kelola Kelas') }}</div>
                                            <div style="font-size: 0.72rem; color: #64748b; font-weight: 400;">{{ auth()->user()->subject_id == 2 ? 'Atur nama grup belajar' : (auth()->user()->subject_id == 3 ? 'Atur nama kelas belajar Matematika' : 'Atur nama kelas belajar') }}</div>
                                        </div>
                                    </a>

                                    <!-- 4. Kelola Pertemuan -->
                                    <a href="{{ route('admin.meetings.index') }}" class="dropdown-item-link" style="display: flex; align-items: center; gap: 12px; padding: 10px 16px; color: #1e293b; font-size: 0.88rem; font-weight: 600; text-decoration: none; transition: background 0.15s;">
                                        <span style="font-size: 1.15rem; width: 22px; text-align: center;">🗓️</span>
                                        <div>
                                            <div>Kelola Pertemuan</div>
                                            <div style="font-size: 0.72rem; color: #64748b; font-weight: 400;">Daftar, tambah & hapus pertemuan belajar</div>
                                        </div>
                                    </a>

                                    <!-- 5. Profil Pengajar -->
                                    <a href="{{ route('admin.classes.index') }}" class="dropdown-item-link" style="display: flex; align-items: center; gap: 12px; padding: 10px 16px; color: #1e293b; font-size: 0.88rem; font-weight: 600; text-decoration: none; transition: background 0.15s;">
                                        <span style="font-size: 1.15rem; width: 22px; text-align: center;">👤</span>
                                        <div>
                                            <div>Profil Pengajar</div>
                                            <div style="font-size: 0.72rem; color: #64748b; font-weight: 400;">Perbarui data diri, email & sandi</div>
                                        </div>
                                    </a>
                                </div>

                                <!-- 5. Tombol Keluar (Inside dropdown) -->
                                <div style="border-top: 1px solid #e2e8f0; padding: 8px 12px; background: #f8fafc;">
                                    <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                                        @csrf
                                        <button type="submit" style="width: 100%; display: flex; align-items: center; gap: 10px; padding: 9px 12px; background: none; border: none; border-radius: var(--radius-sm); color: #dc2626; font-size: 0.88rem; font-weight: 700; cursor: pointer; text-align: left; transition: background 0.15s;" onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='none'">
                                            <span style="font-size: 1.1rem;">🚪</span>
                                            <span>Keluar dari Akun</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- Super Admin Profile Badge & Outside Logout Button --}}
                        <!-- Super Admin Clickable Profile Pill with Dropdown Menu -->
                        <div class="user-dropdown-container" style="position: relative;">
                            <button type="button" id="userDropdownToggle" class="user-profile-badge" style="display: flex; align-items: center; gap: 9px; cursor: pointer; border: 1px solid var(--border-color); background: #ffffff; padding: 5px 14px 5px 8px; border-radius: 9999px; transition: all 0.2s ease; text-align: left;" onclick="toggleUserDropdown(event)" title="Buka Menu Super Admin">
                                <div class="avatar" style="width: 32px; height: 32px; font-size: 0.88rem; font-weight: 800; background: linear-gradient(135deg, #4f46e5, #4338ca); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    S
                                </div>
                                <span style="font-size: 0.9rem; font-weight: 800; color: #0f172a; line-height: 1.2;">
                                    Super Admin
                                </span>
                                <svg id="userDropdownChevron" style="width: 14px; height: 14px; color: #64748b; margin-left: 2px; transition: transform 0.2s;" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>

                            <!-- Dropdown Menu Box -->
                            <div id="userDropdownMenu" style="display: none; position: absolute; right: 0; top: calc(100% + 8px); width: 280px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: var(--radius-lg); box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.12), 0 8px 10px -6px rgba(0, 0, 0, 0.08); z-index: 1000; overflow: hidden;">
                                <!-- Header Info -->
                                <div style="padding: 14px 16px; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                    <div style="font-size: 0.74rem; text-transform: uppercase; font-weight: 800; color: #64748b; letter-spacing: 0.5px;">Akun Super Administrator</div>
                                    <div style="font-size: 0.95rem; font-weight: 800; color: #0f172a; margin-top: 2px;">{{ auth()->user()->name }}</div>
                                    <div style="font-size: 0.78rem; color: #475569; word-break: break-all;">{{ auth()->user()->email }}</div>
                                    <div style="margin-top: 6px;">
                                        <span class="badge" style="background: #eef2ff; color: #4338ca; font-size: 0.72rem; font-weight: 800;">
                                            🛡️ Super Admin
                                        </span>
                                    </div>
                                </div>

                                <!-- Menu Items -->
                                <div style="padding: 6px 0;">
                                    <!-- Riwayat Absensi -->
                                    <a href="{{ route('superadmin.attendance.index') }}" class="dropdown-item-link" style="display: flex; align-items: center; gap: 12px; padding: 10px 16px; color: #1e293b; font-size: 0.88rem; font-weight: 600; text-decoration: none; transition: background 0.15s;">
                                        <span style="font-size: 1.15rem; width: 22px; text-align: center;">⏱️</span>
                                        <div>
                                            <div>Riwayat Absensi</div>
                                            <div style="font-size: 0.72rem; color: #64748b; font-weight: 400;">Rekap absen guru & siswa 3 mapel</div>
                                        </div>
                                    </a>
                                </div>

                                <!-- Tombol Keluar (Inside dropdown) -->
                                <div style="border-top: 1px solid #e2e8f0; padding: 8px 12px; background: #f8fafc;">
                                    <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                                        @csrf
                                        <button type="submit" style="width: 100%; display: flex; align-items: center; gap: 10px; padding: 9px 12px; background: none; border: none; border-radius: var(--radius-sm); color: #dc2626; font-size: 0.88rem; font-weight: 700; cursor: pointer; text-align: left; transition: background 0.15s;" onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='none'">
                                            <span style="font-size: 1.1rem;">🚪</span>
                                            <span>Keluar dari Akun</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Mobile Navigation Toggle Button -->
                    <button type="button" id="mobileNavToggleBtn" class="mobile-nav-toggle-btn" aria-label="Menu Navigasi Mobile" onclick="toggleMobileNavDrawer(event)">
                        <span class="hamburger-line"></span>
                        <span class="hamburger-line"></span>
                        <span class="hamburger-line"></span>
                    </button>
                </div>
            @else
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    @if(!request()->routeIs('login'))
                        <a href="{{ route('login') }}" class="btn btn-primary btn-sm">Masuk (Login)</a>
                    @endif
                    <!-- Mobile Navigation Toggle Button for Guest -->
                    <button type="button" id="mobileNavToggleBtn" class="mobile-nav-toggle-btn" aria-label="Menu Navigasi Mobile" onclick="toggleMobileNavDrawer(event)">
                        <span class="hamburger-line"></span>
                        <span class="hamburger-line"></span>
                        <span class="hamburger-line"></span>
                    </button>
                </div>
            @endauth
        </div>
    </header>

    <!-- Mobile Navigation Backdrop & Drawer -->
    <div id="mobileNavBackdrop" class="mobile-nav-backdrop" onclick="closeMobileNavDrawer()"></div>
    <div id="mobileNavDrawer" class="mobile-nav-drawer" role="dialog" aria-modal="true" aria-label="Menu Navigasi Mobile">
        <div class="mobile-drawer-header">
            <a href="{{ auth()->check() ? route('dashboard') : url('/') }}" style="display: flex; align-items: center; gap: 8px; text-decoration: none;">
                <img src="{{ asset('images/bola.png') }}" alt="Logo" style="height: 32px; width: auto; object-fit: contain;">
                <img src="{{ asset('images/Gambar1.png') }}" alt="MUSASHI" style="height: 24px; width: auto; object-fit: contain;">
            </a>
            <button type="button" onclick="closeMobileNavDrawer()" aria-label="Tutup Menu" style="background: none; border: none; font-size: 1.4rem; color: #64748b; cursor: pointer; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 8px;">
                ✕
            </button>
        </div>

        @auth
            <div class="mobile-drawer-user">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                    <div class="avatar" style="width: 42px; height: 42px; font-size: 1.05rem; font-weight: 800; background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div style="overflow: hidden;">
                        <div style="font-weight: 800; font-size: 0.96rem; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            {{ auth()->user()->name }}
                        </div>
                        <div style="font-size: 0.78rem; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            {{ auth()->user()->email }}
                        </div>
                    </div>
                </div>
                <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                    @if(auth()->user()->isSuperAdmin())
                        <span class="badge" style="background: #eef2ff; color: #4338ca; font-weight: 800; font-size: 0.72rem;">
                            🛡️ Super Admin
                        </span>
                    @elseif(auth()->user()->isAdmin())
                        @php
                            $adminSubj = match((int)auth()->user()->subject_id) {
                                2 => ['label' => 'Guru Bhs Jepang', 'bg' => '#fce7f3', 'color' => '#9d174d'],
                                3 => ['label' => 'Guru Matematika', 'bg' => '#dcfce7', 'color' => '#166534'],
                                default => ['label' => 'Guru Bhs Inggris', 'bg' => '#dbeafe', 'color' => '#1e40af'],
                            };
                        @endphp
                        <span class="badge" style="background: {{ $adminSubj['bg'] }}; color: {{ $adminSubj['color'] }}; font-weight: 800; font-size: 0.72rem;">
                            👨‍🏫 {{ $adminSubj['label'] }}
                        </span>
                    @else
                        @php
                            $stSubj = match((int)(session('active_subject_id') ?? auth()->user()->subject_id ?? 1)) {
                                2 => ['label' => 'Bhs Jepang', 'bg' => '#fce7f3', 'color' => '#9d174d'],
                                3 => ['label' => 'Matematika', 'bg' => '#dcfce7', 'color' => '#166534'],
                                default => ['label' => 'Bhs Inggris', 'bg' => '#dbeafe', 'color' => '#1e40af'],
                            };
                        @endphp
                        <span class="badge" style="background: {{ $stSubj['bg'] }}; color: {{ $stSubj['color'] }}; font-weight: 800; font-size: 0.72rem;">
                            🎓 {{ $stSubj['label'] }}
                        </span>
                        @if(auth()->user()->class_name)
                            <span class="badge" style="background: #f1f5f9; color: #334155; font-size: 0.72rem; font-weight: 700;">
                                {{ auth()->user()->class_name }}
                            </span>
                        @endif
                    @endif
                </div>
            </div>

            <ul class="mobile-drawer-menu">
                <div class="mobile-drawer-section-title">Menu Utama</div>
                @if(auth()->user()->isSuperAdmin())
                    <li class="mobile-drawer-item">
                        <a href="{{ route('superadmin.dashboard') }}" class="{{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}" onclick="closeMobileNavDrawer()">
                            <span>📊</span> <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="mobile-drawer-item">
                        <a href="{{ route('superadmin.admins.index') }}" class="{{ request()->routeIs('superadmin.admins.*') ? 'active' : '' }}" onclick="closeMobileNavDrawer()">
                            <span>👩‍🏫</span> <span>Kelola Admin</span>
                        </a>
                    </li>
                    <li class="mobile-drawer-item">
                        <a href="{{ route('superadmin.students.index') }}" class="{{ request()->routeIs('superadmin.students.*') ? 'active' : '' }}" onclick="closeMobileNavDrawer()">
                            <span>👥</span> <span>Kelola Siswa</span>
                        </a>
                    </li>
                    <li class="mobile-drawer-item">
                        <a href="{{ route('superadmin.questions.index') }}" class="{{ request()->routeIs('superadmin.questions.*') ? 'active' : '' }}" onclick="closeMobileNavDrawer()">
                            <span>📝</span> <span>Kelola Soal</span>
                        </a>
                    </li>
                    <li class="mobile-drawer-item">
                        <a href="{{ route('superadmin.attendance.index') }}" class="{{ request()->routeIs('superadmin.attendance.*') ? 'active' : '' }}" onclick="closeMobileNavDrawer()">
                            <span>⏱️</span> <span>Riwayat Absensi 3 Mapel</span>
                        </a>
                    </li>
                @elseif(auth()->user()->isAdmin())
                    <li class="mobile-drawer-item">
                        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" onclick="closeMobileNavDrawer()">
                            <span>📊</span> <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="mobile-drawer-item">
                        <a href="{{ route('admin.materials.index') }}" class="{{ request()->routeIs('admin.materials.*') ? 'active' : '' }}" onclick="closeMobileNavDrawer()">
                            <span>📚</span> <span>Materi Pembelajaran</span>
                        </a>
                    </li>
                    @if(auth()->user()->subject_id == 1)
                        <li class="mobile-drawer-item">
                            <a href="{{ route('admin.grades.index') }}" class="{{ request()->routeIs('admin.grades.*') ? 'active' : '' }}" onclick="closeMobileNavDrawer()">
                                <span>⭐</span> <span>Nilai / Feedback</span>
                            </a>
                        </li>
                    @elseif(auth()->user()->subject_id == 2)
                        <li class="mobile-drawer-item">
                            <a href="{{ route('admin.japanese.tests.index') }}" class="{{ request()->routeIs('admin.japanese.tests.*') ? 'active' : '' }}" onclick="closeMobileNavDrawer()">
                                <span>📝</span> <span>Latihan Soal & Tes Jepang</span>
                            </a>
                        </li>
                    @else
                        <li class="mobile-drawer-item">
                            <a href="{{ route('admin.exercises.index') }}" class="{{ request()->routeIs('admin.exercises.*') ? 'active' : '' }}" onclick="closeMobileNavDrawer()">
                                <span>📝</span> <span>Latihan Soal Matematika</span>
                            </a>
                        </li>
                    @endif
                    <div class="mobile-drawer-section-title">Manajemen Kelas & Siswa</div>
                    <li class="mobile-drawer-item">
                        <a href="{{ route('admin.students.index') }}" class="{{ request()->routeIs('admin.students.*') ? 'active' : '' }}" onclick="closeMobileNavDrawer()">
                            <span>👥</span> <span>Kelola Siswa</span>
                        </a>
                    </li>
                    <li class="mobile-drawer-item">
                        <a href="{{ route('admin.attendance.index') }}" class="{{ request()->routeIs('admin.attendance.*') ? 'active' : '' }}" onclick="closeMobileNavDrawer()">
                            <span>⏱️</span> <span>Kelola Absensi</span>
                        </a>
                    </li>
                    <li class="mobile-drawer-item">
                        <a href="{{ route('admin.classes.index') }}" class="{{ request()->routeIs('admin.classes.*') ? 'active' : '' }}" onclick="closeMobileNavDrawer()">
                            <span>🏫</span> <span>{{ auth()->user()->subject_id == 2 ? 'Kelola Grup' : 'Kelola Kelas' }}</span>
                        </a>
                    </li>
                    <li class="mobile-drawer-item">
                        <a href="{{ route('admin.meetings.index') }}" class="{{ request()->routeIs('admin.meetings.*') ? 'active' : '' }}" onclick="closeMobileNavDrawer()">
                            <span>🗓️</span> <span>Kelola Pertemuan</span>
                        </a>
                    </li>
                @else
                    {{-- Siswa --}}
                    <li class="mobile-drawer-item">
                        <a href="{{ route('student.dashboard') }}" class="{{ request()->routeIs('student.dashboard') ? 'active' : '' }}" onclick="closeMobileNavDrawer()">
                            <span>🏠</span> <span>Beranda</span>
                        </a>
                    </li>
                    <li class="mobile-drawer-item">
                        <a href="{{ route('student.materials.index') }}" class="{{ request()->routeIs('student.materials.*') ? 'active' : '' }}" onclick="closeMobileNavDrawer()">
                            <span>📚</span> <span>Materi Pelajaran</span>
                        </a>
                    </li>
                    <li class="mobile-drawer-item">
                        <a href="{{ auth()->user()->subject_id == 2 ? route('student.japanese.tests.index') : route('student.exercises.index') }}" class="{{ request()->routeIs('student.exercises.*') || request()->routeIs('student.japanese.tests.*') ? 'active' : '' }}" onclick="closeMobileNavDrawer()">
                            <span>🎯</span> <span>Latihan Soal</span>
                        </a>
                    </li>
                    <div class="mobile-drawer-section-title">Akun & Riwayat Belajar</div>
                    <li class="mobile-drawer-item">
                        <a href="{{ route('student.attendance.index') }}" class="{{ request()->routeIs('student.attendance.*') ? 'active' : '' }}" onclick="closeMobileNavDrawer()">
                            <span>⏱️</span> <span>Riwayat Presensi Siswa</span>
                        </a>
                    </li>
                    @if(auth()->user()->subject_id == 2)
                        <li class="mobile-drawer-item">
                            <a href="{{ route('student.japanese.grades.index') }}" class="{{ request()->routeIs('student.japanese.grades.*') ? 'active' : '' }}" onclick="closeMobileNavDrawer()">
                                <span>📊</span> <span>Riwayat Skor Tes Evaluasi</span>
                            </a>
                        </li>
                    @elseif(auth()->user()->subject_id == 1)
                        <li class="mobile-drawer-item">
                            <a href="{{ route('student.grades.index') }}" class="{{ request()->routeIs('student.grades.*') ? 'active' : '' }}" onclick="closeMobileNavDrawer()">
                                <span>📊</span> <span>Riwayat Skor & Feedback</span>
                            </a>
                        </li>
                    @endif
                    <li class="mobile-drawer-item">
                        <a href="{{ route('student.profile.edit') }}" class="{{ request()->routeIs('student.profile.*') ? 'active' : '' }}" onclick="closeMobileNavDrawer()">
                            <span>👤</span> <span>Profil Saya & Sandi</span>
                        </a>
                    </li>
                @endif

                <li style="margin-top: auto; padding-top: 1rem; border-top: 1px solid #f1f5f9;">
                    <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                        @csrf
                        <button type="submit" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; padding: 11px; background: #fee2e2; border: 1px solid #fecaca; border-radius: var(--radius-md); color: #dc2626; font-weight: 700; font-size: 0.92rem; cursor: pointer;">
                            <span>🚪</span>
                            <span>Keluar dari Akun</span>
                        </button>
                    </form>
                </li>
            </ul>
        @else
            <ul class="mobile-drawer-menu">
                <li class="mobile-drawer-item">
                    <a href="{{ url('/') }}" onclick="closeMobileNavDrawer()">
                        <span>🏠</span> <span>Halaman Utama</span>
                    </a>
                </li>
                <li class="mobile-drawer-item" style="margin-top: 1rem;">
                    <a href="{{ route('login') }}" class="btn btn-primary" style="justify-content: center; color: #fff;" onclick="closeMobileNavDrawer()">
                        <span>🔑 Masuk ke Akun (Login)</span>
                    </a>
                </li>
            </ul>
        @endauth
    </div>

    <!-- Main Content Area -->
    <main class="main-content">
        <div class="container">
            <!-- Flash Message Alerts -->
            @if(session('success'))
                <div class="alert alert-success">
                    <span>{{ session('success') }}</span>
                    <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;font-size:1.1rem;color:#065f46;">&times;</button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    <span>{{ session('error') }}</span>
                    <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;font-size:1.1rem;color:#9f1239;">&times;</button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info">
                    <span>{{ session('info') }}</span>
                    <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;font-size:1.1rem;color:#1e40af;">&times;</button>
                </div>
            @endif

            <!-- Content Slot -->
            @yield('content')
        </div>
    </main>

    <!-- Level-Up Celebration Modal -->
    @if(session('level_up'))
        <div class="modal-overlay" id="levelUpModal">
            <div class="modal-content">
                <div style="font-size: 3.5rem; margin-bottom: 0.5rem; animation: bounce 1s infinite alternate;">🎉 🏆 🚀</div>
                <h2 style="font-size: 1.8rem; margin-bottom: 0.5rem; color: #0f172a;">SELAMAT NAIK LEVEL!</h2>
                <p style="color: var(--text-muted); margin-bottom: 1.5rem; font-size: 1.05rem;">
                    Anda telah menguasai level sebelumnya dengan 100 poin sempurna! Sekarang Anda resmi membuka 
                    <strong style="color: var(--color-primary);">{{ session('level_up')['level_name'] }}</strong> 
                    pada mata pelajaran <strong>{{ session('level_up')['subject_name'] }}</strong>.
                </p>
                <div style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: var(--radius-md); padding: 12px; margin-bottom: 1.5rem;">
                    <div style="font-size: 0.85rem; color: #475569; font-weight: 600;">
                        Catatan: Progres poin telah di-reset ke 0 untuk memulai tantangan level baru!
                    </div>
                </div>
                <button type="button" class="btn btn-primary btn-lg" onclick="document.getElementById('levelUpModal').style.display='none'">
                    Mulai Belajar di Level Baru!
                </button>
            </div>
        </div>
    @endif

    <!-- Footer -->
    <footer class="footer">
        <div class="container" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
            <div>
                <strong>Musashi Learning Platform</strong> &copy; {{ date('Y') }} &bull; Sistem Pembelajaran Bertingkat Berbasis Latihan & Poin
            </div>
            <div style="display: flex; gap: 1.5rem; font-size: 0.85rem;">
                <span>Bahasa Inggris</span>
                <span>Bahasa Jepang</span>
            </div>
        </div>
    </footer>

    <script>
    function toggleUserDropdown(e) {
        if (e) e.stopPropagation();
        const menu = document.getElementById('userDropdownMenu');
        const chevron = document.getElementById('userDropdownChevron');
        if (!menu) return;
        const isOpen = menu.style.display === 'block';
        menu.style.display = isOpen ? 'none' : 'block';
        if (chevron) {
            chevron.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
        }
    }

    function toggleMobileNavDrawer(e) {
        if (e) e.stopPropagation();
        const drawer = document.getElementById('mobileNavDrawer');
        const backdrop = document.getElementById('mobileNavBackdrop');
        const toggleBtn = document.getElementById('mobileNavToggleBtn');
        if (!drawer) return;
        const isOpen = drawer.classList.contains('open');
        if (isOpen) {
            closeMobileNavDrawer();
        } else {
            drawer.classList.add('open');
            if (backdrop) backdrop.classList.add('open');
            if (toggleBtn) toggleBtn.classList.add('active');
            document.body.classList.add('drawer-open');
        }
    }

    function closeMobileNavDrawer() {
        const drawer = document.getElementById('mobileNavDrawer');
        const backdrop = document.getElementById('mobileNavBackdrop');
        const toggleBtn = document.getElementById('mobileNavToggleBtn');
        if (drawer) drawer.classList.remove('open');
        if (backdrop) backdrop.classList.remove('open');
        if (toggleBtn) toggleBtn.classList.remove('active');
        document.body.classList.remove('drawer-open');
    }

    document.addEventListener('click', function(e) {
        const menu = document.getElementById('userDropdownMenu');
        const toggle = document.getElementById('userDropdownToggle');
        if (menu && menu.style.display === 'block') {
            if (!menu.contains(e.target) && (!toggle || !toggle.contains(e.target))) {
                menu.style.display = 'none';
                const chevron = document.getElementById('userDropdownChevron');
                if (chevron) chevron.style.transform = 'rotate(0deg)';
            }
        }

        const drawer = document.getElementById('mobileNavDrawer');
        const toggleBtn = document.getElementById('mobileNavToggleBtn');
        if (drawer && drawer.classList.contains('open')) {
            if (!drawer.contains(e.target) && (!toggleBtn || !toggleBtn.contains(e.target))) {
                closeMobileNavDrawer();
            }
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeMobileNavDrawer();
            const menu = document.getElementById('userDropdownMenu');
            if (menu) {
                menu.style.display = 'none';
                const chevron = document.getElementById('userDropdownChevron');
                if (chevron) chevron.style.transform = 'rotate(0deg)';
            }
        }
    });
    </script>
    @stack('scripts')
</body>
</html>
