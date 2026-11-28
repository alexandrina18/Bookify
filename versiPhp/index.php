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
page_head('Perpustakaan SMA GLITZ');
?>
<section class="hero">
  <div style="display:inline-block;margin-bottom:14px;">
    <img src="<?= e(url('assets/sma-glitz-logo.jpg')) ?>" alt="Logo SMA GLITZ" style="width:105px;height:105px;object-fit:contain;border-radius:20px;padding:6px;background:#ffffff;box-shadow:0 12px 30px rgba(0,0,0,0.12);border:2px solid #e2e8f0;">
  </div>
  <h1>Selamat Datang di <span class="gradient">SMA GLITZ</span></h1>
  <p>Sistem perpustakaan digital terpadu SMA GLITZ untuk menemukan koleksi buku pelajaran, fiksi, dan referensi belajar siswa.</p>
  <div class="hero-actions"><a class="button btn btn-primary" href="<?= e(url('login.php')) ?>">Mulai Meminjam</a><a href="#catalog">Lihat Katalog</a></div>
</section>
<section class="container" style="padding-top:0"><div class="stats">
  <div class="stat"><strong><?= $totalBooks ?>+</strong><small>Koleksi Buku</small></div>
  <div class="stat"><strong><?= $members ?>+</strong><small>Siswa Terdaftar</small></div>
  <div class="stat"><strong>2020</strong><small>Tahun Berdiri</small></div>
  <div class="stat"><strong><?= $availableBooks ?>+</strong><small>Buku Tersedia</small></div>
</div></section>
<section id="catalog" class="container"><h2 class="section-title">Katalog <span class="gradient">Buku SMA GLITZ</span></h2><p class="muted">Temukan buku yang kamu cari dari koleksi perpustakaan kami</p>
  <form class="search" method="get"><input name="search" value="<?= e($search) ?>" placeholder="Cari judul, pengarang, genre, atau ID buku..."><button class="btn btn-primary">Cari</button></form>
  <div class="grid">
  <?php foreach ($books as $book): ?>
    <?php $cUrl = !empty($book['cover_url']) ? (str_starts_with($book['cover_url'], 'http') ? $book['cover_url'] : url($book['cover_url'])) : ''; ?>
    <article class="card book-card">
      <?php if ($cUrl): ?>
        <div style="height:190px;width:100%;border-radius:10px;overflow:hidden;background:#e9dfd0;margin-bottom:12px;box-shadow:0 4px 10px rgba(0,0,0,0.06);">
          <img src="<?= e($cUrl) ?>" alt="Sampul <?= e($book['title']) ?>" style="width:100%;height:100%;object-fit:cover;display:block;">
        </div>
      <?php else: ?>
        <div class="book-icon"></div>
      <?php endif; ?>
      <div style="margin-top:4px">
        <span class="genre"><?= e($book['genre'] ?: 'Umum') ?></span>
        <h3><?= e($book['title']) ?></h3>
        <p class="muted" style="margin:0"><?= e($book['author']) ?></p>
        <?php if ($book['publisher_name']): ?><small class="muted"><?= e($book['publisher_name']) ?></small><?php endif; ?>
        <p class="<?= $book['available'] > 0 ? 'available' : 'unavailable' ?>"><b><?= $book['available'] > 0 ? $book['available'] . ' tersedia' : 'Tidak tersedia' ?></b> <small class="muted">· <?= e($book['book_id']) ?></small></p>
        <?php $bookReviews=rows('SELECT r.rating,r.review,r.created_at,u.name FROM reviews r JOIN users u ON u.id=r.user_id JOIN books k ON k.id=r.book_id WHERE r.book_id=? ORDER BY r.created_at DESC LIMIT 2',[$book['id']]); foreach($bookReviews as $review):?>
          <p class="landing-review"><b><?=str_repeat('★',(int)$review['rating'])?></b> <?=e($review['review'])?><small> — <?=e($review['name'])?></small></p>
        <?php endforeach;?>
      </div>
    </article>
  <?php endforeach; ?>
  <?php if (!$books): ?><p class="muted">Tidak ada buku ditemukan.</p><?php endif; ?>
  </div>
</section>
<section class="container"><div class="card"><h2 class="section-title">Tentang <span class="gradient">Perpustakaan SMA GLITZ</span></h2><p class="muted">Perpustakaan SMA GLITZ menyediakan berbagai koleksi literatur dan kemudahan peminjaman bagi seluruh siswa sekolah.</p><div class="form-grid"><p><b>Lokasi</b><br><span class="muted">Gedung Perpustakaan SMA GLITZ, Jl. Glitz No. 1, Semarang</span></p><p><b>Jam Operasional</b><br><span class="muted">Senin–Jumat 07.30–16.00 · Sabtu 08.00–12.00</span></p></div></div></section>
<footer class="footer">© 2026 Perpustakaan SMA GLITZ — Sistem Peminjaman Terpadu</footer>
<?php page_foot(); ?>
