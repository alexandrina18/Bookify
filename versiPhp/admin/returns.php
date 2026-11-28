<?php
require_once __DIR__ . '/../includes/layout.php'; require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $member = one('SELECT * FROM users WHERE member_id=?', [trim($_POST['member_id'] ?? '')]);
    $book = one('SELECT * FROM books WHERE book_id=?', [trim($_POST['book_id'] ?? '')]);
    $borrowing = $member && $book ? one('SELECT * FROM borrowings WHERE user_id=? AND book_id=? AND status IN ("active","overdue") ORDER BY borrowed_at DESC', [$member['id'], $book['id']]) : null;
    if (!$borrowing) flash('Peminjaman aktif tidak ditemukan.', 'error');
    else {
        $late = max(0, (int) floor((time() - strtotime($borrowing['due_date'])) / 86400));
        execute('UPDATE borrowings SET returned_at=CURRENT_TIMESTAMP,status="returned" WHERE id=?', [$borrowing['id']]);
        execute('UPDATE books SET available=available+1 WHERE id=?', [$book['id']]);
        if ($late > 0) execute('INSERT INTO fines (user_id,borrowing_id,days_late,amount,status,type) VALUES (?,?,?, ?,"unpaid","late")', [$member['id'], $borrowing['id'], $late, $late * 1000]);
        flash('Pengembalian berhasil. ' . ($late ? 'Denda: ' . rupiah($late * 1000) : 'Tidak ada denda.'));
    }
    redirect('admin/returns.php');
}
$memberId = trim($_GET['member_id'] ?? ''); $bookId = trim($_GET['book_id'] ?? '');
$query = 'SELECT br.*,u.name,u.member_id,b.title,b.book_id FROM borrowings br JOIN users u ON u.id=br.user_id JOIN books b ON b.id=br.book_id WHERE br.status="returned"'; $params = [];
if ($memberId !== '') { $query .= ' AND u.member_id LIKE ?'; $params[] = '%' . $memberId . '%'; }
if ($bookId !== '') { $query .= ' AND b.book_id LIKE ?'; $params[] = '%' . $bookId . '%'; }
$data = rows($query . ' ORDER BY br.returned_at DESC', $params);
$returnMembers = rows('SELECT DISTINCT u.member_id,u.nis,u.kelas,u.name FROM borrowings br JOIN users u ON u.id=br.user_id WHERE br.status IN ("active","overdue") ORDER BY u.name');
$returnBooks = rows('SELECT DISTINCT b.book_id,b.title FROM borrowings br JOIN books b ON b.id=br.book_id WHERE br.status IN ("active","overdue") ORDER BY b.title');
page_head('Pengembalian Buku', true); admin_heading('Pengembalian Buku', 'Kembalikan buku siswa SMA GLITZ dan hitung denda otomatis bila terlambat');
?>
<section class="modal"><form method="post"><div class="form-grid"><div class="form-row"><label>Siswa dengan Pinjaman Aktif</label><select name="member_id" required><option value="">Pilih siswa</option><?php foreach($returnMembers as $memberOption): ?><option value="<?= e($memberOption['member_id']) ?>"><?= e($memberOption['name'].' ('.$memberOption['kelas'].' · NIS: '.($memberOption['nis']?:$memberOption['member_id']).')') ?></option><?php endforeach; ?></select></div><div class="form-row"><label>Buku yang Dipinjam</label><select name="book_id" required><option value="">Pilih buku</option><?php foreach($returnBooks as $bookOption): ?><option value="<?= e($bookOption['book_id']) ?>"><?= e($bookOption['book_id'].' · '.$bookOption['title']) ?></option><?php endforeach; ?></select></div></div><button class="btn btn-success">Proses Pengembalian</button></form></section>
<form class="card admin-id-search" method="get"><input name="member_id" value="<?= e($memberId) ?>" placeholder="Filter nama atau NIS siswa"><input name="book_id" value="<?= e($bookId) ?>" placeholder="Filter ID buku"><button type="submit" class="btn btn-primary">Terapkan filter</button><a class="button secondary btn btn-secondary" href="<?= e(url('admin/returns.php')) ?>">Reset</a></form>
<section class="card table-card"><table><thead><tr><th>Siswa</th><th>Buku</th><th>Dikembalikan</th></tr></thead><tbody><?php foreach ($data as $row): ?><tr><td><?= e($row['name']) ?> <small class="muted"><?= e($row['member_id']) ?></small></td><td><?= e($row['title']) ?></td><td><?= date_id($row['returned_at'], true) ?></td></tr><?php endforeach; ?><?php if (!$data): ?><tr><td colspan="3" class="muted">Tidak ada data pengembalian.</td></tr><?php endif; ?></tbody></table></section>
<?php page_foot(true); ?>
