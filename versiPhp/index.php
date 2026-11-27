<?php
require_once __DIR__ . '/includes/layout.php';
$signedIn = current_user();
if ($signedIn && $signedIn['role'] === 'member') { redirect('member/dashboard.php'); }
$search = trim($_GET['search'] ?? '');
$sql = 'SELECT b.*, p.name AS publisher_name FROM books b LEFT JOIN publishers p ON p.id=b.publisher_id';
$params = [];
if ($search !== '') {
    $sql .= ' WHERE b.title LIKE ? OR b.author LIKE ? OR b.genre LIKE ? OR b.book_id LIKE ?';
    $needle = '%' . $search . '%'; $params = [$needle, $needle, $needle, $needle];
}
$books = rows($sql . ' ORDER BY b.title', $params);
$totalBooks = count_of('books');
$availableBooks = count_of('books', 'available > 0');
$members = count_of('users', "role='member' AND status='active'");
page_head('Perpustakaan Digital');
?>
<section class="hero">
  <h1>Selamat Datang di <span class="gradient">Bookify</span></h1>
  <p>Sistem perpustakaan digital terpadu untuk menemukan koleksi, meminjam buku, dan mengelola aktivitas perpustakaan dalam satu tempat.</p>
  <div class="hero-actions"><a class="button btn btn-primary" href="<?= e(url('login.php')) ?>">Mulai Meminjam</a><a href="#catalog">Lihat Katalog</a></div>
</section>
<section class="container" style="padding-top:0"><div class="stats">
  <div class="stat"><strong><?= $totalBooks ?>+</strong><small>Koleksi Buku</small></div>
  <div class="stat"><strong><?= $members ?>+</strong><small>Anggota Aktif</small></div>
  <div class="stat"><strong>2020</strong><small>Tahun Berdiri</small></div>
  <div class="stat"><strong><?= $availableBooks ?>+</strong><small>Buku Tersedia</small></div>
</div></section>
<section id="catalog" class="container"><h2 class="section-title">Katalog <span class="gradient">Buku</span></h2><p class="muted">Temukan buku yang kamu cari dari koleksi kami</p>
  <form class="search" method="get"><input name="search" value="<?= e($search) ?>" placeholder="Cari judul, pengarang, genre, atau ID buku..."><button class="btn btn-primary">Cari</button></form>
  <div class="grid">
  <?php foreach ($books as $book): ?>
    <article class="card book-card"><div class="book-icon">▤</div><div style="margin-top:12px"><span class="genre"><?= e($book['genre'] ?: 'Umum') ?></span><h3><?= e($book['title']) ?></h3><p class="muted" style="margin:0"><?= e($book['author']) ?></p><?php if ($book['publisher_name']): ?><small class="muted"><?= e($book['publisher_name']) ?></small><?php endif; ?><p class="<?= $book['available'] > 0 ? 'available' : 'unavailable' ?>"><b><?= $book['available'] > 0 ? $book['available'] . ' tersedia' : 'Tidak tersedia' ?></b> <small class="muted">· <?= e($book['book_id']) ?></small></p><?php $bookReviews=rows('SELECT r.rating,r.review,r.created_at,u.name FROM reviews r JOIN users u ON u.id=r.user_id JOIN books k ON k.id=r.book_id WHERE r.book_id=? ORDER BY r.created_at DESC LIMIT 2',[$book['id']]); foreach($bookReviews as $review):?><p class="landing-review"><b><?=str_repeat('★',(int)$review['rating'])?></b> <?=e($review['review'])?><small> — <?=e($review['name'])?></small></p><?php endforeach;?></div></article>
  <?php endforeach; ?>
  <?php if (!$books): ?><p class="muted">Tidak ada buku ditemukan.</p><?php endif; ?>
  </div>
</section>
<section class="container"><div class="card"><h2 class="section-title">Tentang <span class="gradient">Perpustakaan Kami</span></h2><p class="muted">Bookify menyediakan koleksi berkualitas dan layanan mudah diakses untuk seluruh anggota.</p><div class="form-grid"><p><b>Lokasi</b><br><span class="muted">Jl. Pawiyatan Luhur No.1, Semarang</span></p><p><b>Jam Buka</b><br><span class="muted">Senin–Jumat 08.00–17.00 · Sabtu 08.00–13.00</span></p></div></div></section>
<footer class="footer">© 2026 Bookify — Kelompok Jolanda Theodora Alexandrina</footer>
<?php page_foot(); ?>
