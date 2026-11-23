<?php
require_once __DIR__ . '/../includes/layout.php'; require_admin();
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $member=one('SELECT * FROM users WHERE member_id=?',[trim($_POST['member_id']??'')]); $book=one('SELECT * FROM books WHERE book_id=?',[trim($_POST['book_id']??'')]);
  $borrowing=$member&&$book?one('SELECT * FROM borrowings WHERE user_id=? AND book_id=? AND status IN ("active","overdue") ORDER BY borrowed_at DESC',[$member['id'],$book['id']]):null;
  if(!$borrowing) flash('Peminjaman aktif tidak ditemukan.','error'); else { $late=max(0,(int)floor((time()-strtotime($borrowing['due_date']))/86400)); execute('UPDATE borrowings SET returned_at=CURRENT_TIMESTAMP,status="returned" WHERE id=?',[$borrowing['id']]); execute('UPDATE books SET available=available+1 WHERE id=?',[$book['id']]); if($late>0) execute('INSERT INTO fines (user_id,borrowing_id,days_late,amount,status,type) VALUES (?,?,?,?,"unpaid","late")',[$member['id'],$borrowing['id'],$late,$late*1000]); flash('Pengembalian berhasil. ' . ($late ? 'Denda: '.rupiah($late*1000) : 'Tidak ada denda.')); }
  redirect('admin/returns.php');
}
$data=rows('SELECT br.*,u.name,u.member_id,b.title,b.book_id FROM borrowings br JOIN users u ON u.id=br.user_id JOIN books b ON b.id=br.book_id WHERE br.status="returned" ORDER BY br.returned_at DESC'); page_head('Pengembalian',true); admin_heading('Pengembalian','Kembalikan buku dan hitung denda keterlambatan otomatis');
?>
<section class="modal"><form method="post"><div class="form-grid"><div class="form-row"><label>ID Anggota</label><input name="member_id" placeholder="MBR001" required></div><div class="form-row"><label>ID Buku</label><input name="book_id" placeholder="BK001" required></div></div><button class="success">Proses Pengembalian</button></form></section><section class="card table-card"><table><thead><tr><th>Anggota</th><th>Buku</th><th>Dikembalikan</th></tr></thead><tbody><?php foreach($data as $row): ?><tr><td><?=e($row['name'])?> <small class="muted"><?=e($row['member_id'])?></small></td><td><?=e($row['title'])?></td><td><?=date_id($row['returned_at'],true)?></td></tr><?php endforeach; ?></tbody></table></section><?php page_foot(true); ?>
