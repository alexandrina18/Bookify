<?php
require_once __DIR__ . '/../includes/layout.php'; require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $extension = one('SELECT * FROM extensions WHERE id=?', [(int) $_POST['id']]); $status = $_POST['status'];
    if ($extension) {
        if ($status === 'approved') {
            $loan=one('SELECT due_date FROM borrowings WHERE id=?',[$extension['borrowing_id']]);
            $duration=max(1,(int)($extension['duration_days']??7));
            $newDue=(new DateTimeImmutable($loan['due_date']))->modify('+'.$duration.' days')->format('Y-m-d H:i:s');
            execute('UPDATE borrowings SET due_date=?,renew_count=renew_count+1,status="active" WHERE id=?',[$newDue,$extension['borrowing_id']]);
            execute('UPDATE extensions SET status=?,admin_note=?,reviewed_at=CURRENT_TIMESTAMP,new_due_date=? WHERE id=?', [$status, trim($_POST['admin_note'] ?? ''), $newDue, $extension['id']]);
        } else execute('UPDATE extensions SET status=?,admin_note=?,reviewed_at=CURRENT_TIMESTAMP WHERE id=?', [$status, trim($_POST['admin_note'] ?? ''), $extension['id']]);
        flash('Permintaan perpanjangan ditinjau.');
    }
    redirect('admin/extensions.php');
}
$memberId = trim($_GET['member_id'] ?? ''); $bookId = trim($_GET['book_id'] ?? '');
$sql = 'SELECT e.*,u.name,u.member_id,b.title,b.book_id,br.due_date,br.renew_count FROM extensions e JOIN users u ON u.id=e.user_id JOIN borrowings br ON br.id=e.borrowing_id JOIN books b ON b.id=br.book_id WHERE u.member_id LIKE ? AND b.book_id LIKE ? ORDER BY e.requested_at DESC';
$data = rows($sql, ['%' . $memberId . '%', '%' . $bookId . '%']);
page_head('Perpanjangan', true); admin_heading('Perpanjangan', 'Tinjau permintaan perpanjangan masa pinjam');
?>
<form class="toolbar admin-id-search" method="get"><input name="member_id" value="<?= e($memberId) ?>" placeholder="Cari ID anggota"><input name="book_id" value="<?= e($bookId) ?>" placeholder="Cari ID buku"><button type="submit">Cari</button><a class="button secondary" href="<?= e(url('admin/extensions.php')) ?>">Reset</a></form>
<section class="card table-card"><table><thead><tr><th>Anggota</th><th>Buku</th><th>Jatuh tempo</th><th>Durasi / alasan</th><th>Perpanjangan</th><th>Status</th><th>Aksi</th></tr></thead><tbody><?php foreach ($data as $row): ?><tr><td><?= e($row['name']) ?><br><small class="muted"><?= e($row['member_id']) ?></small></td><td><?= e($row['title']) ?><br><small class="muted"><?= e($row['book_id']) ?></small></td><td><?= date_id($row['due_date']) ?></td><td><?= (int)($row['duration_days']??7) ?> hari<br><?= e($row['reason']??'') ?></td><td><?= e($row['renew_count']) ?>x</td><td><?= status_badge($row['status']) ?></td><td><?php if ($row['status'] === 'pending'): ?><form method="post" class="actions"><input type="hidden" name="id" value="<?= (int) $row['id'] ?>"><input name="admin_note" placeholder="Catatan"><button name="status" value="approved" class="success">Setujui</button><button name="status" value="rejected" class="danger">Tolak</button></form><?php endif; ?></td></tr><?php endforeach; ?><?php if (!$data): ?><tr><td colspan="7" class="muted">Tidak ada permintaan perpanjangan.</td></tr><?php endif; ?></tbody></table></section>
<?php page_foot(true); ?>
