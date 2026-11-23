<?php
require_once __DIR__ . '/../includes/layout.php';
$user = require_login();
if ($user['role'] === 'admin') redirect('admin/dashboard.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['extension_id'])) {
    $borrowing = one('SELECT * FROM borrowings WHERE id=? AND user_id=?', [(int) $_POST['extension_id'], $user['id']]);
    if ($borrowing && $borrowing['renew_count'] < 6 && strtotime($borrowing['due_date']) >= strtotime('-2 days')) {
        execute('INSERT INTO extensions (user_id, borrowing_id) VALUES (?,?)', [$user['id'], $borrowing['id']]); flash('Permintaan perpanjangan telah dikirim.');
    } else flash('Perpanjangan belum dapat diajukan.', 'error');
    redirect('member/dashboard.php');
}
$borrowings = rows('SELECT b.*, k.title, k.book_id FROM borrowings b JOIN books k ON k.id=b.book_id WHERE b.user_id=? ORDER BY b.borrowed_at DESC', [$user['id']]);
$books = rows('SELECT * FROM books ORDER BY available DESC, title LIMIT 12');
$categories = rows("SELECT DISTINCT genre FROM books WHERE genre IS NOT NULL AND genre <> '' ORDER BY genre");
$featured = $books[0] ?? null;
$activeCount = count(array_filter($borrowings, static fn($item) => in_array($item['status'], ['active', 'overdue'], true)));
page_head('Dashboard Anggota');
?>
<main class="member-app">
    <aside class="member-sidebar">
        <a class="member-brand" href="<?= e(url('index.php')) ?>"><img class="member-brand-logo" src="<?= e(url('assets/bookify-logo.png')) ?>" alt=""> Bookify</a>
        <nav class="member-menu" aria-label="Navigasi anggota">
            <a class="selected" href="<?= e(url('member/dashboard.php')) ?>"><span>⌂</span> Jelajahi</a>
            <a href="#kategori"><span>▤</span> Kategori</a>
            <a href="#peminjaman"><span>▣</span> Peminjaman saya <i><?= $activeCount ?></i></a>
            <a href="#koleksi"><span>♡</span> Koleksi buku</a>
            <a href="#peminjaman"><span>◷</span> Riwayat</a>
        </nav>
        <div class="member-menu-bottom">
            <a href="<?= e(url('index.php')) ?>"><span>⚙</span> Beranda</a>
            <a href="<?= e(url('logout.php')) ?>"><span>⇥</span> Keluar</a>
        </div>
        <div class="member-profile"><span class="profile-avatar"><?= e(strtoupper(substr($user['name'], 0, 1))) ?></span><span><b><?= e($user['name']) ?></b><small><?= e($user['member_id']) ?></small></span><span class="profile-more">⌄</span></div>
    </aside>
    <section class="member-content">
        <header class="member-topbar">
            <form class="member-search" action="<?= e(url('index.php')) ?>#catalog" method="get"><span>⌕</span><input type="search" name="q" placeholder="Cari buku favoritmu..."></form>
            <div class="member-greeting"><span class="member-notice" aria-label="Notifikasi">♧<i></i></span><span class="profile-avatar small-avatar"><?= e(strtoupper(substr($user['name'], 0, 1))) ?></span><b><?= e($user['name']) ?></b></div>
        </header>
        <div class="member-main">
            <section class="member-section" id="koleksi">
                <div class="member-section-heading"><div><span class="member-kicker">PILIHAN UNTUKMU</span><h1>Rekomendasi Buku</h1></div><a class="member-see-all" href="<?= e(url('index.php#catalog')) ?>">Lihat katalog <span>→</span></a></div>
                <?php if ($books): ?><div class="member-book-grid">
                    <?php foreach (array_slice($books, 0, 4) as $index => $book): ?>
                        <article class="member-book-card"><div class="member-cover cover-<?= $index + 1 ?>">
                            <?php if (!empty($book['cover_url'])): ?><img src="<?= e($book['cover_url']) ?>" alt="Sampul <?= e($book['title']) ?>"><?php else: ?><span class="cover-mark">BOOKIFY<br><b><?= e(strtoupper(substr($book['title'], 0, 1))) ?></b></span><?php endif; ?>
                        </div><div class="member-book-info"><h3><?= e($book['title']) ?></h3><p><?= e($book['author']) ?></p><span class="member-availability <?= (int) $book['available'] > 0 ? 'in-stock' : 'out-stock' ?>"><?= (int) $book['available'] > 0 ? 'Tersedia' : 'Sedang dipinjam' ?></span></div></article>
                    <?php endforeach; ?>
                </div><?php else: ?><p class="member-empty">Belum ada buku di katalog.</p><?php endif; ?>
            </section>
            <section class="member-section category-section" id="kategori">
                <div class="member-section-heading"><div><span class="member-kicker">TEMUKAN BACAAN BARU</span><h2>Kategori</h2></div><span class="category-count"><?= count($categories) ?> kategori</span></div>
                <div class="member-category-list"><a class="active" href="#koleksi">Semua</a><?php foreach ($categories as $category): ?><a href="<?= e(url('index.php#catalog')) ?>"><?= e($category['genre']) ?></a><?php endforeach; ?></div>
                <div class="member-book-grid compact-grid">
                    <?php foreach (array_slice($books, 4, 4) as $index => $book): ?><article class="member-book-card compact-book"><div class="member-cover cover-<?= ($index + 5) ?>">
                        <?php if (!empty($book['cover_url'])): ?><img src="<?= e($book['cover_url']) ?>" alt="Sampul <?= e($book['title']) ?>"><?php else: ?><span class="cover-mark">BOOKIFY<br><b><?= e(strtoupper(substr($book['title'], 0, 1))) ?></b></span><?php endif; ?>
                    </div><div class="member-book-info"><h3><?= e($book['title']) ?></h3><p><?= e($book['author']) ?></p></div></article><?php endforeach; ?>
                </div>
            </section>
            <section class="member-section loans-section" id="peminjaman">
                <div class="member-section-heading"><div><span class="member-kicker">AKTIVITASMU</span><h2>Peminjaman Saya</h2></div><span class="loan-total"><?= count($borrowings) ?> buku</span></div>
                <div class="card table-card member-loans"><table><thead><tr><th>Buku</th><th>Dipinjam</th><th>Jatuh tempo</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
                <?php foreach ($borrowings as $item): ?><tr><td><b><?= e($item['title']) ?></b><br><small class="muted"><?= e($item['book_id']) ?></small></td><td><?= date_id($item['borrowed_at']) ?></td><td><?= date_id($item['due_date']) ?></td><td><?= status_badge($item['status']) ?></td><td><?php if (in_array($item['status'], ['active','overdue'], true)): ?><form method="post"><input type="hidden" name="extension_id" value="<?= (int) $item['id'] ?>"><button class="renew-button">Ajukan perpanjangan</button></form><?php else: ?>—<?php endif; ?></td></tr><?php endforeach; ?>
                <?php if (!$borrowings): ?><tr><td colspan="5" class="muted">Belum ada peminjaman.</td></tr><?php endif; ?></tbody></table></div>
            </section>
        </div>
    </section>
    <aside class="member-feature-panel">
        <?php if ($featured): ?><div class="feature-label">BUKU PILIHAN</div><div class="feature-cover member-cover cover-feature">
            <?php if (!empty($featured['cover_url'])): ?><img src="<?= e($featured['cover_url']) ?>" alt="Sampul <?= e($featured['title']) ?>"><?php else: ?><span class="cover-mark">BOOKIFY<br><b><?= e(strtoupper(substr($featured['title'], 0, 1))) ?></b></span><?php endif; ?>
        </div><span class="feature-genre"><?= e($featured['genre'] ?: 'Koleksi Bookify') ?></span><h2><?= e($featured['title']) ?></h2><p class="feature-author"><?= e($featured['author']) ?></p><div class="feature-rating"><span>★★★★★</span> <b>4.8</b></div><div class="feature-stats"><span><b><?= (int) ($featured['pages'] ?: 0) ?></b>Halaman</span><span><b><?= (int) ($featured['available'] ?: 0) ?></b>Tersedia</span><span><b><?= e($featured['year'] ?: '—') ?></b>Tahun</span></div><p class="feature-description">Temukan koleksi pilihan Bookify dan jelajahi bacaan menarik dari berbagai kategori di katalog kami.</p><a class="feature-button" href="<?= e(url('index.php#catalog')) ?>">Jelajahi katalog</a><?php else: ?><p>Belum ada buku pilihan.</p><?php endif; ?>
    </aside>
</main>
<?php page_foot(); ?>
