<?php
require_once __DIR__ . '/../includes/layout.php';
if (current_user() && current_user()['role'] === 'admin') redirect('admin/dashboard.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (login(trim($_POST['member_id'] ?? ''), $_POST['password'] ?? '', 'admin')) redirect('admin/dashboard.php');
    $error = 'ID Admin atau password salah.';
}
page_head('Login Admin SMA GLITZ');
?>
<main class="login-wrap"><section class="card login-card"><a class="brand login-brand" href="<?= e(url('index.php')) ?>"><img class="brand-logo" src="<?= e(url('assets/sma-glitz-logo.jpg')) ?>" alt="Logo SMA GLITZ"> <span>SMA GLITZ</span></a><p style="color:#fbbf24">Portal Petugas Perpustakaan</p><h1>Admin Login</h1><p>Akses panel administrasi perpustakaan SMA GLITZ</p>
<?php if ($error): ?><p class="notice"><?= e($error) ?></p><?php endif; ?>
<form method="post"><div class="form-row"><label>ID Admin</label><input name="member_id" placeholder="Contoh: ADMIN001" required autofocus></div><div class="form-row"><label>Password</label><input type="password" name="password" required></div><button class="btn btn-warning btn-block">Masuk sebagai Admin</button></form>
<p class="muted" style="margin-top:20px">Demo: <b>ADMIN001</b> / <b>admin123</b></p><p style="text-align:center"><a class="muted" href="<?= e(url('login.php')) ?>">← Login sebagai Siswa</a></p></section></main>
<?php page_foot(); ?>
