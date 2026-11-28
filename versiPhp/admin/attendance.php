<?php
require_once __DIR__ . '/../includes/layout.php'; require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = one('SELECT * FROM users WHERE member_id=?', [trim($_POST['member_id'] ?? '')]);
    $active = $user ? one('SELECT * FROM attendances WHERE user_id=? AND check_out IS NULL ORDER BY check_in DESC', [$user['id']]) : null;
    if (!$user) flash('Anggota tidak ditemukan.', 'error');
    elseif ($user['status'] === 'blocked') flash('Akun anggota diblokir.', 'error');
    elseif ($active) { execute('UPDATE attendances SET check_out=CURRENT_TIMESTAMP,duration=CAST((julianday(CURRENT_TIMESTAMP)-julianday(check_in))*1440 AS INTEGER) WHERE id=?', [$active['id']]); flash('Check-out berhasil.'); }
    else { execute('INSERT INTO attendances (user_id) VALUES (?)', [$user['id']]); flash('Check-in berhasil.'); }
    redirect('admin/attendance.php');
}
$memberId = trim($_GET['member_id'] ?? '');
$data = rows('SELECT a.*,u.name,u.member_id FROM attendances a JOIN users u ON u.id=a.user_id WHERE u.member_id LIKE ? ORDER BY a.check_in DESC LIMIT 100', ['%' . $memberId . '%']);
$members = rows('SELECT member_id,nis,name,kelas FROM users WHERE role="member" AND status="active" ORDER BY name');
page_head('Absensi Siswa', true); admin_heading('Absensi Kunjungan', 'Check-in dan check-out kehadiran siswa di perpustakaan SMA GLITZ');
?>
<section class="modal"><form method="post"><div class="form-row"><label>Pilih Siswa</label><select name="member_id" required><option value="">Pilih siswa</option><?php foreach($members as $memberOption): ?><option value="<?= e($memberOption['member_id']) ?>"><?= e($memberOption['name'].' ('.$memberOption['kelas'].' · NIS: '.($memberOption['nis']?:$memberOption['member_id']).')') ?></option><?php endforeach; ?></select></div><button class="btn btn-primary">Check-in / Check-out</button></form></section>
<form class="card admin-id-search" method="get"><input name="member_id" value="<?= e($memberId) ?>" placeholder="Filter ID / NIS siswa"><button type="submit" class="btn btn-primary">Terapkan filter</button><a class="button secondary btn btn-secondary" href="<?= e(url('admin/attendance.php')) ?>">Reset</a></form>
<section class="card table-card"><table><thead><tr><th>Siswa</th><th>Masuk</th><th>Keluar</th><th>Durasi</th></tr></thead><tbody><?php foreach ($data as $row): ?><tr><td><?= e($row['name']) ?><br><small class="muted"><?= e($row['member_id']) ?></small></td><td><?= date_id($row['check_in'], true) ?></td><td><?= date_id($row['check_out'], true) ?></td><td><?= $row['duration'] === null ? status_badge('active') : e($row['duration'] . ' menit') ?></td></tr><?php endforeach; ?><?php if (!$data): ?><tr><td colspan="4" class="muted">Tidak ada data absensi.</td></tr><?php endif; ?></tbody></table></section>
<?php page_foot(true); ?>
