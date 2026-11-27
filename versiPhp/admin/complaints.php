<?php
require_once __DIR__ . '/../includes/layout.php'; require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') { execute('UPDATE complaints SET status=?,admin_note=? WHERE id=?', [$_POST['status'], trim($_POST['admin_note'] ?? ''), (int) $_POST['id']]); flash('Pengaduan diperbarui.'); redirect('admin/complaints.php'); }
$memberId = trim($_GET['member_id'] ?? ''); $bookId = trim($_GET['book_id'] ?? '');
$sql = 'SELECT c.*,u.name,u.member_id,b.title,b.book_id FROM complaints c JOIN users u ON u.id=c.user_id JOIN books b ON b.id=c.book_id WHERE u.member_id LIKE ? AND b.book_id LIKE ? ORDER BY c.created_at DESC';
$data = rows($sql, ['%' . $memberId . '%', '%' . $bookId . '%']);
page_head('Pengaduan', true); admin_heading('Pengaduan', 'Tinjau laporan buku rusak atau hilang');
?>
<form class="toolbar admin-id-search" method="get"><input name="member_id" value="<?= e($memberId) ?>" placeholder="Cari ID anggota"><input name="book_id" value="<?= e($bookId) ?>" placeholder="Cari ID buku"><button type="submit" class="btn btn-primary">Cari</button><a class="button secondary btn btn-secondary" href="<?= e(url('admin/complaints.php')) ?>">Reset</a></form>
<section class="card table-card"><table><thead><tr><th>Anggota</th><th>Buku</th><th>Jenis</th><th>Deskripsi</th><th>Status</th><th>Aksi</th></tr></thead><tbody><?php foreach ($data as $row): ?><tr><td><?= e($row['name']) ?><br><small class="muted"><?= e($row['member_id']) ?></small></td><td><?= e($row['title']) ?><br><small class="muted"><?= e($row['book_id']) ?></small></td><td><?= e($row['type']) ?></td><td><?= e($row['description']) ?></td><td><?= status_badge($row['status']) ?></td><td><?php if ($row['status'] === 'pending'): ?><form method="post" class="actions"><input type="hidden" name="id" value="<?= (int) $row['id'] ?>"><input name="admin_note" placeholder="Catatan"><button name="status" value="resolved" class="success">Selesai</button></form><?php endif; ?></td></tr><?php endforeach; ?><?php if (!$data): ?><tr><td colspan="6" class="muted">Tidak ada pengaduan.</td></tr><?php endif; ?></tbody></table></section>
<?php page_foot(true); ?>
