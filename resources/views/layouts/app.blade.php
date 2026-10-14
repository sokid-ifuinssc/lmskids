<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'LMS Ujian') — UIN Siber</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body>
    <div class="app">
        <!-- Sidebar Navigation -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-brand">
                <div class="brand-logo">
                    <i class="ri-graduation-cap-fill"></i>
                </div>
                <div>
                    <div class="brand-name">UIN Siber</div>
                    <div class="brand-sub">{{ auth()->user()->isAdmin() ? 'Dashboard Dosen' : 'Portal Mahasiswa' }}</div>
                </div>
            </div>

            <!-- User Profile Card -->
            <div class="sidebar-user">
                <div class="avatar">
                    <i class="ri-user-smile-line"></i>
                </div>
                <div style="min-width: 0;">
                    <div class="name">{{ auth()->user()->name }}</div>
                    <div class="meta">{{ auth()->user()->isAdmin() ? 'NIP: ' : 'NIM: ' }}{{ auth()->user()->username }}</div>
                    @if(auth()->user()->isMahasiswa() && auth()->user()->kelas)
                        <div class="meta" style="color: #6ee7b7; font-weight: 500;">Kelas: {{ auth()->user()->kelas->nama }}</div>
                    @endif
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="nav">
                @if(auth()->user()->isAdmin())
                    <div class="nav-label">Menu Dosen</div>
                    <a href="{{ route('admin.ujian.index') }}" class="{{ request()->routeIs('admin.ujian.*') ? 'active' : '' }}">
                        <i class="ri-file-list-3-line"></i>
                        <span>Kelola Ujian</span>
                    </a>
                    <a href="{{ route('admin.soal.index') }}" class="{{ request()->routeIs('admin.soal.*') ? 'active' : '' }}">
                        <i class="ri-questionnaire-line"></i>
                        <span>Kelola Soal</span>
                    </a>
                    <a href="{{ route('admin.mahasiswa.index') }}" class="{{ request()->routeIs('admin.mahasiswa.*') ? 'active' : '' }}">
                        <i class="ri-group-line"></i>
                        <span>Data Pengguna</span>
                    </a>
                    <a href="{{ route('admin.matakuliah.index') }}" class="{{ request()->routeIs('admin.matakuliah.*') ? 'active' : '' }}">
                        <i class="ri-book-read-line"></i>
                        <span>Mata Kuliah</span>
                    </a>
                    <a href="{{ route('admin.kelas.index') }}" class="{{ request()->routeIs('admin.kelas.*') ? 'active' : '' }}">
                        <i class="ri-community-line"></i>
                        <span>Kelola Kelas</span>
                    </a>
                    <a href="{{ route('admin.rekap') }}" class="{{ request()->routeIs('admin.rekap*') ? 'active' : '' }}">
                        <i class="ri-bar-chart-box-line"></i>
                        <span>Laporan Nilai</span>
                    </a>
                    <a href="{{ route('profil') }}" class="{{ request()->routeIs('profil') ? 'active' : '' }}">
                        <i class="ri-user-settings-line"></i>
                        <span>Profil Saya</span>
                    </a>
                @else
                    <div class="nav-label">Menu Mahasiswa</div>
                    <a href="{{ route('mhs.dashboard') }}" class="{{ request()->routeIs('mhs.dashboard') ? 'active' : '' }}">
                        <i class="ri-dashboard-3-line"></i>
                        <span>Daftar Ujian</span>
                    </a>
                    <a href="{{ route('mhs.hasil') }}" class="{{ request()->routeIs('mhs.hasil') ? 'active' : '' }}">
                        <i class="ri-award-line"></i>
                        <span>Nilai & Riwayat</span>
                    </a>
                    <a href="{{ route('profil') }}" class="{{ request()->routeIs('profil') ? 'active' : '' }}">
                        <i class="ri-user-settings-line"></i>
                        <span>Profil Saya</span>
                    </a>
                @endif
            </nav>

            <!-- Logout Button -->
            <div class="sidebar-footer">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit">
                        <i class="ri-logout-box-r-line"></i>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Backdrop for Mobile -->
        <div class="backdrop" id="backdrop" onclick="toggleSidebar()"></div>

        <!-- Main Content Area -->
        <div class="main">
            <!-- Topbar with Title & Live Clock -->
            <header class="topbar">
                <button class="menu-toggle" id="menuToggle" onclick="toggleSidebar()" aria-label="Toggle Menu">
                    <i class="ri-menu-2-line"></i>
                </button>

                <div class="topbar-title">
                    <h1>@yield('page-title', 'Dashboard')</h1>
                    <div class="crumb">
                        <span>Dashboard {{ auth()->user()->isAdmin() ? 'Dosen' : 'Mahasiswa' }}</span>
                        @hasSection('page-subtitle')
                            <span> &rsaquo; </span>
                            <span>@yield('page-subtitle')</span>
                        @endif
                    </div>
                </div>

                <div class="clock" id="liveClock">--.--.--</div>
            </header>

            <!-- Page Body -->
            <main class="content">
                <!-- Flash Alerts -->
                @if(session('success'))
                    <div class="alert alert-success">
                        <i class="ri-checkbox-circle-fill"></i>
                        <div>{{ session('success') }}</div>
                        <button type="button" class="close" onclick="this.parentElement.remove()">&times;</button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-error">
                        <i class="ri-error-warning-fill"></i>
                        <div>{{ session('error') }}</div>
                        <button type="button" class="close" onclick="this.parentElement.remove()">&times;</button>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warn">
                        <i class="ri-alert-fill"></i>
                        <div>{{ session('warning') }}</div>
                        <button type="button" class="close" onclick="this.parentElement.remove()">&times;</button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-error">
                        <i class="ri-error-warning-fill"></i>
                        <div>
                            <strong>Terdapat beberapa kesalahan:</strong>
                            <ul>
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <button type="button" class="close" onclick="this.parentElement.remove()">&times;</button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Live Clock Script & Mobile Toggle -->
    <script>
        function updateClock() {
            const now = new Date();
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');
            const clockEl = document.getElementById('liveClock');
            if (clockEl) clockEl.textContent = `${h}.${m}.${s}`;
        }
        setInterval(updateClock, 1000);
        updateClock();

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('backdrop');
            if (sidebar) sidebar.classList.toggle('open');
            if (backdrop) backdrop.classList.toggle('show');
        }
    </script>
    @stack('scripts')
</body>
</html>
