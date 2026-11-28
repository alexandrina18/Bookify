<?php
require_once __DIR__ . '/includes/layout.php';
if (current_user()) redirect(current_user()['role'] === 'admin' ? 'admin/dashboard.php' : 'member/dashboard.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (login(trim($_POST['member_id'] ?? ''), $_POST['password'] ?? '', 'member')) redirect('member/dashboard.php');
    $error = 'ID anggota atau password salah.';
}
page_head('Login Siswa SMA GLITZ');
?>
<main class="login-wrap"><section class="card login-card"><a class="brand login-brand" href="<?= e(url('index.php')) ?>"><img class="brand-logo" src="<?= e(url('assets/sma-glitz-logo.jpg')) ?>" alt="Logo SMA GLITZ"> <span>SMA GLITZ</span></a><h1>Login Siswa</h1><p>Masuk menggunakan NIS atau ID Siswa kamu</p>
<?php if ($error): ?><p class="notice"><?= e($error) ?></p><?php endif; ?>
<form method="post"><div class="form-row"><label>NIS / ID Siswa</label><input name="member_id" placeholder="Contoh: 20241001 atau MBR001" required autofocus></div><div class="form-row"><label>Password</label><input type="password" name="password" placeholder="Masukkan password" required></div><div class="login-actions"><button class="btn btn-primary">Masuk</button><a class="button secondary login-cancel" href="<?= e(url('index.php')) ?>">Batal</a></div></form>
<p class="muted" style="margin-top:20px">Demo Siswa: <b>20241001</b> (atau <b>MBR001</b>) / <b>member123</b></p><p style="text-align:center"><a class="muted" href="<?= e(url('admin/login.php')) ?>">Login sebagai Admin Perpustakaan →</a></p></section></main>
<?php page_foot(); ?>
