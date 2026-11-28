<?php
require_once __DIR__ . '/../includes/member.php';
$user = require_login();
if ($user['role'] === 'admin') redirect('admin/dashboard.php');

expire_pickup_reservations((int)$user['id']);
expire_unpaid_delivery_requests((int)$user['id']);

$book = one('SELECT b.*, p.name as publisher_name FROM books b LEFT JOIN publishers p ON p.id=b.publisher_id WHERE b.id=?', [(int)($_GET['book'] ?? $_POST['book_id'] ?? 0)]);

// Ambil data lengkap siswa dari database
$student = one('SELECT * FROM users WHERE id=?', [(int)$user['id']]);
$studentName = $student['name'] ?? $user['name'];
$studentNis = $student['nis'] ?? $student['member_id'] ?? $user['member_id'];
$studentClass = $student['kelas'] ?? 'Siswa SMA GLITZ';
$studentAddress = $student['address'] ?? 'Semarang';
$studentPhone = $student['phone'] ?? '-';
$studentEmail = $student['email'] ?? '-';

// Hitung durasi dan jatuh tempo
$now = new DateTimeImmutable('now', new DateTimeZone('Asia/Jakarta'));
$dueDate = $now->modify('+7 days')->setTime(17, 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$book || (int)$book['available'] < 1) {
        flash('Buku tidak tersedia untuk dipinjam.', 'error');
        redirect('member/katalog.php');
    }

    // Cek batas pinjaman siswa (maksimal 3 buku)
    $activeCount = count_of('borrowings', 'user_id=? AND status IN ("active","overdue")', [$user['id']]);
    if ($activeCount >= 3) {
        flash('Batas peminjaman maksimal 3 buku. Selesaikan peminjaman sebelumnya terlebih dahulu.', 'error');
        redirect('member/peminjaman.php');
    }

    // Cek denda belum lunas
    $hasUnpaidFine = count_of('fines', 'user_id=? AND status="unpaid"', [$user['id']]);
    if ($hasUnpaidFine > 0) {
        flash('Anda memiliki denda yang belum dibayar. Silakan lunasi denda sebelum meminjam.', 'error');
        redirect('member/denda.php');
    }

    pdo()->beginTransaction();
    try {
        execute(
            'INSERT INTO borrowings(user_id, book_id, borrowed_at, due_date, status, fulfillment_method, delivery_address) VALUES(?,?,?,?,"active","sekolah",?)',
            [
                $user['id'],
                $book['id'],
                $now->format('Y-m-d H:i:s'),
                $dueDate->format('Y-m-d H:i:s'),
                $studentAddress
            ]
        );
        execute('UPDATE books SET available = available - 1 WHERE id=? AND available > 0', [$book['id']]);
        pdo()->commit();

        flash('Peminjaman buku berhasil dicatat untuk ' . $studentName . ' (' . $studentClass . '). Harap kembalikan sebelum ' . date_id($dueDate->format('Y-m-d H:i:s'), false) . '.');
        redirect('member/peminjaman.php');
    } catch (Throwable $error) {
        pdo()->rollBack();
        throw $error;
    }
}

member_start('Detail Peminjaman', $user, 'katalog.php');
?>
<section class="member-section detail-pinjam-form">
    <div class="member-section-heading">
        <div>
            <span class="member-kicker">PERPUSTAKAAN SMA GLITZ</span>
            <h1>Konfirmasi Peminjaman Buku</h1>
        </div>
    </div>

    <?php if ($book): ?>
        <div class="card" style="margin-bottom:20px;border-left:4px solid #0861ef;display:flex;gap:18px;align-items:center;">
            <?php $cUrl = !empty($book['cover_url']) ? (str_starts_with($book['cover_url'], 'http') ? $book['cover_url'] : url($book['cover_url'])) : ''; ?>
            <?php if ($cUrl): ?>
                <img src="<?= e($cUrl) ?>" alt="Sampul <?= e($book['title']) ?>" style="width:70px;height:95px;object-fit:cover;border-radius:6px;box-shadow:0 3px 8px rgba(0,0,0,0.12);flex-shrink:0;">
            <?php endif; ?>
            <div style="flex:1;">
                <h3 style="margin:0 0 6px 0;font-size:17px;color:#10213f;"><?= e($book['title']) ?></h3>
                <p class="muted" style="margin:0 0 10px 0;">
                    Penulis: <b><?= e($book['author']) ?></b> · ID Buku: <b><?= e($book['book_id']) ?></b>
                    <?php if (!empty($book['publisher_name'])): ?> · Penerbit: <?= e($book['publisher_name']) ?><?php endif; ?>
                </p>
                <div style="display:flex;gap:12px;font-size:13px;flex-wrap:wrap;">
                    <span class="badge badge-active"><?= (int)$book['available'] ?> eksemplar tersedia</span>
                    <span class="badge" style="background:#edf4ff;color:#075fe8;">Durasi Pinjam: 7 Hari</span>
                    <span class="badge" style="background:#fef3c7;color:#92400e;">Jatuh Tempo: <?= date_id($dueDate->format('Y-m-d H:i:s'), false) ?></span>
                </div>
            </div>
        </div>

        <!-- Data Siswa Terdeteksi Otomatis -->
        <div class="card" style="background:#f8faff;border:1px solid #dbeafe;border-radius:12px;padding:20px;margin-bottom:20px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;border-bottom:1px solid #e2e8f0;padding-bottom:10px;">
                <h3 style="margin:0;font-size:15px;color:#1e3a8a;">
                    Data Siswa Terverifikasi (Terdeteksi Otomatis)
                </h3>
                <div style="display:flex;align-items:center;gap:8px;">
                    <img src="<?= e(url('assets/sma-glitz-logo.jpg')) ?>" alt="Logo SMA GLITZ" style="width:32px;height:32px;object-fit:contain;border-radius:7px;background:#fff;padding:2px;border:1px solid #cbd5e1;">
                    <span style="font-size:11px;background:#10b98120;color:#059669;padding:3px 8px;border-radius:99px;font-weight:700;">SMA GLITZ</span>
                </div>
            </div>

            <div class="form-grid" style="grid-template-columns:repeat(2,1fr);gap:14px;">
                <div>
                    <label style="margin-bottom:3px;font-size:11px;color:#64748b;">NAMA SISWA</label>
                    <div style="font-size:15px;font-weight:700;color:#0f172a;"><?= e($studentName) ?></div>
                </div>
                <div>
                    <label style="margin-bottom:3px;font-size:11px;color:#64748b;">NIS / NO. INDUK</label>
                    <div style="font-size:15px;font-weight:700;color:#0f172a;"><?= e($studentNis) ?></div>
                </div>
                <div>
                    <label style="margin-bottom:3px;font-size:11px;color:#64748b;">KELAS / JURUSAN</label>
                    <div style="font-size:14px;font-weight:600;color:#2563eb;"><?= e($studentClass) ?></div>
                </div>
                <div>
                    <label style="margin-bottom:3px;font-size:11px;color:#64748b;">NO. TELEPON / WA</label>
                    <div style="font-size:14px;color:#334155;"><?= e($studentPhone) ?></div>
                </div>
                <div style="grid-column:1/-1;">
                    <label style="margin-bottom:3px;font-size:11px;color:#64748b;">ALAMAT LENGKAP SISWA (OTOMATIS TERDETEKSI)</label>
                    <div style="font-size:14px;font-weight:500;color:#1e293b;background:#ffffff;padding:10px 12px;border:1px solid #cbd5e1;border-radius:8px;">
                        <?= e($studentAddress) ?>
                    </div>
                    <small style="color:#64748b;font-size:11px;margin-top:4px;display:block;">
                        *Data alamat langsung terhubung dengan data master siswa SMA GLITZ. Siswa tidak perlu menginput alamat manual.
                    </small>
                </div>
            </div>
        </div>

        <form method="post" id="loan-confirm-form">
            <input type="hidden" name="book_id" value="<?= (int)$book['id'] ?>">
            <div style="display:flex;align-items:center;gap:12px;margin-top:20px;">
                <button type="submit" <?= ((int)$book['available'] < 1 ? 'disabled' : '') ?> class="btn btn-primary" style="padding:12px 24px;font-size:14px;">
                    Konfirmasi Peminjaman Buku
                </button>
                <a href="<?= e(url('member/katalog.php')) ?>" class="button secondary btn btn-secondary" style="padding:12px 20px;">
                    Kembali ke Katalog
                </a>
            </div>
        </form>
    <?php else: ?>
        <p>Buku tidak ditemukan atau sudah tidak tersedia.</p>
        <a href="<?= e(url('member/katalog.php')) ?>" class="button btn btn-primary">Kembali ke Katalog</a>
    <?php endif; ?>
</section>
<?php member_end(); ?>
