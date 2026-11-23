<?php
require_once __DIR__ . '/../includes/layout.php';
require_admin();
$stats = [
    ['Total Buku', count_of('books'), '#818cf8'], ['Total Anggota', count_of('users', "role='member'"), '#c084fc'],
    ['Total Penerbit', count_of('publishers'), '#34d399'], ['Pengunjung Aktif', count_of('attendances', 'check_out IS NULL'), '#fbbf24'],
    ['Dipinjam (Aktif)', count_of('borrowings', "status='active'"), '#60a5fa'], ['Terlambat', count_of('borrowings', "status='overdue'"), '#f87171'],
    ['Perpanjangan Pending', count_of('extensions', "status='pending'"), '#fb923c'], ['Total Denda', rupiah((int) (one("SELECT COALESCE(SUM(amount),0) AS amount FROM fines WHERE status='unpaid'")['amount'])), '#f472b6'],
];
$recent = rows('SELECT br.*, u.name, u.member_id, b.title, b.book_id FROM borrowings br JOIN users u ON u.id=br.user_id JOIN books b ON b.id=br.book_id ORDER BY br.borrowed_at DESC LIMIT 5');
$chart=[]; for ($i=6;$i>=0;$i--) { $date=date('Y-m-d', strtotime("-$i days")); $chart[]=['label'=>date('d/m',strtotime($date)), 'total'=>count_of('borrowings','date(borrowed_at)=?',[$date])]; }
page_head('Dashboard', true); admin_heading('Dashboard', 'Ringkasan aktivitas perpustakaan hari ini');
?>
<section class="dashboard-stats"><?php foreach ($stats as [$label,$value,$color]): ?><div class="stat"><strong style="color:<?= e($color) ?>"><?= e($value) ?></strong><small><?= e($label) ?></small></div><?php endforeach; ?></section>
<section class="card" style="margin-bottom:24px"><h2 style="margin-top:0">Grafik Peminjaman</h2><p class="muted">7 hari terakhir</p><div class="chart"><?php $max=max(array_column($chart,'total')) ?: 1; foreach ($chart as $day): ?><div class="bar" title="<?= $day['total'] ?> peminjaman" style="height:<?= max(12, ($day['total'] / $max) * 125) ?>px"><span><?= e($day['label']) ?></span></div><?php endforeach; ?></div></section>
<section class="card table-card"><h2>Transaksi Terbaru</h2><table><thead><tr><th>Anggota</th><th>Buku</th><th>Tanggal Pinjam</th><th>Jatuh Tempo</th><th>Status</th></tr></thead><tbody><?php foreach ($recent as $row): ?><tr><td><b><?= e($row['name']) ?></b><br><small class="muted"><?= e($row['member_id']) ?></small></td><td><?= e($row['title']) ?><br><small class="muted"><?= e($row['book_id']) ?></small></td><td><?= date_id($row['borrowed_at']) ?></td><td><?= date_id($row['due_date']) ?></td><td><?= status_badge($row['status']) ?></td></tr><?php endforeach; ?></tbody></table></section>
<?php page_foot(true); ?>
