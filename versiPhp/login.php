<?php
require_once __DIR__ . '/includes/layout.php';
if (current_user()) redirect(current_user()['role'] === 'admin' ? 'admin/dashboard.php' : 'member/dashboard.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (login(trim($_POST['member_id'] ?? ''), $_POST['password'] ?? '', 'member')) redirect('member/dashboard.php');
    $error = 'ID anggota atau password salah.';
}
page_head('Login Anggota');
?>
<main class="login-wrap"><section class="card login-card"><a class="brand" style="display:block;text-align:center" href="<?= e(url('index.php')) ?>">▣ <span>Bookify</span></a><h1>Selamat Datang!</h1><p>Login dengan ID Anggota kamu</p>
<?php if ($error): ?><p class="notice"><?= e($error) ?></p><?php endif; ?>
<form method="post"><div class="form-row"><label>ID Anggota</label><input name="member_id" placeholder="Contoh: MBR001" required autofocus></div><div class="form-row"><label>Password</label><input type="password" name="password" placeholder="Masukkan password" required></div><button style="width:100%">Masuk</button></form>
<p class="muted" style="margin-top:20px">Demo: <b>MBR001</b> / <b>member123</b></p><p style="text-align:center"><a class="muted" href="<?= e(url('admin/login.php')) ?>">Login sebagai Admin →</a></p></section></main>
<?php page_foot(); ?>
