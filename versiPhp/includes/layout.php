<?php
require_once __DIR__ . '/bootstrap.php';

function page_head(string $title, bool $admin = false): void
{
    $fullTitle = e($title) . ' · ' . APP_NAME;
    echo '<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . $fullTitle . '</title><link rel="stylesheet" href="' . e(url('assets/style.css')) . '"></head><body>';
    if ($admin) { admin_nav(); } else { public_nav(); }
    $flash = pull_flash();
    if ($flash) { echo '<div class="flash ' . e($flash['type']) . '">' . e($flash['message']) . '</div>'; }
}

function public_nav(): void
{
    $user = current_user();
    echo '<nav class="navbar"><a class="brand" href="' . e(url('index.php')) . '"><img class="brand-logo" src="' . e(url('assets/sma-glitz-logo.jpg')) . '" alt="Logo SMA GLITZ"> <span>SMA GLITZ</span></a><div class="nav-links">';
    echo '<a href="' . e(url('index.php#catalog')) . '">Katalog</a>';
    if ($user) {
        echo '<a href="' . e(url($user['role'] === 'admin' ? 'admin/dashboard.php' : 'member/dashboard.php')) . '">Dashboard</a><a href="' . e(url('logout.php')) . '">Keluar</a>';
    } else {
        echo '<a href="' . e(url('login.php')) . '">Login Siswa</a><a class="button" href="' . e(url('admin/login.php')) . '">Admin</a>';
    }
    echo '</div></nav>';
}

function admin_nav(): void
{
    $user = require_admin();
    $links = [
        'dashboard.php' => 'Dashboard', 'borrowing.php' => 'Peminjaman', 'returns.php' => 'Pengembalian', 'attendance.php' => 'Absensi',
        'books.php' => 'Buku', 'publishers.php' => 'Penerbit', 'members.php' => 'Data Siswa', 'fines.php' => 'Denda',
        'extensions.php' => 'Perpanjangan', 'complaints.php' => 'Pengaduan', 'budget.php' => 'Anggaran',
    ];
    $current = basename($_SERVER['PHP_SELF'] ?? '');
    echo '<aside class="sidebar"><a class="brand" href="' . e(url('index.php')) . '"><img class="brand-logo" src="' . e(url('assets/sma-glitz-logo.jpg')) . '" alt="Logo SMA GLITZ"> <span>SMA GLITZ</span></a><small>PERPUSTAKAAN ADMIN</small><nav>';
    foreach ($links as $file => $label) {
        echo '<a class="' . ($current === $file ? 'active' : '') . '" href="' . e(url('admin/' . $file)) . '">' . e($label) . '</a>';
    }
    echo '</nav><div class="sidebar-user"><strong>' . e($user['name']) . '</strong><small>' . e($user['member_id']) . '</small><a href="' . e(url('logout.php')) . '">Keluar</a></div></aside><main class="admin-main">';
}

function page_foot(bool $admin = false): void
{
    if ($admin) { echo '</main>'; }
    echo '<script src="' . e(url('assets/app.js')) . '"></script></body></html>';
}

function admin_heading(string $title, string $subtitle = ''): void
{
    echo '<header class="page-heading"><div><h1>' . e($title) . '</h1>' . ($subtitle ? '<p>' . e($subtitle) . '</p>' : '') . '</div></header>';
}
