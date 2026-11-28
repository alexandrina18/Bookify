<?php
require_once __DIR__ . '/../includes/member.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm_pickup'])) {
        $reservation = one("SELECT * FROM borrowings WHERE id=? AND status='pickup_pending'", [(int)$_POST['confirm_pickup']]);
        if (!$reservation) {
            flash('Permintaan ambil langsung tidak ditemukan.', 'error');
        } elseif (strtotime($reservation['pickup_deadline']) < time()) {
            execute("UPDATE borrowings SET status='cancelled',updated_at=CURRENT_TIMESTAMP WHERE id=?", [$reservation['id']]);
            execute('UPDATE books SET available=available+1 WHERE id=?', [$reservation['book_id']]);
            flash('Batas ambil sudah lewat. Permintaan dibatalkan.', 'error');
        } elseif (count_of('fines', 'user_id=? AND status="unpaid"', [$reservation['user_id']])) {
            flash('Siswa masih memiliki denda yang belum dibayar.', 'error');
        } elseif (count_of('borrowings', 'user_id=? AND status IN ("active","overdue")', [$reservation['user_id']]) >= 3) {
            flash('Batas peminjaman siswa maksimal 3 buku.', 'error');
        } else {
            $start = new DateTimeImmutable('now');
            $due = $start->modify('+7 days')->setTime(17, 0);
            execute("UPDATE borrowings SET status='active',borrowed_at=?,due_date=?,updated_at=CURRENT_TIMESTAMP WHERE id=?", [$start->format('Y-m-d H:i:s'), $due->format('Y-m-d H:i:s'), $reservation['id']]);
            flash('Pengambilan buku dikonfirmasi. Masa pinjam 7 hari dimulai sekarang.');
        }
        redirect('admin/borrowing.php');
    }

    $identifier = trim($_POST['member_id'] ?? '');
    $member = one('SELECT * FROM users WHERE (member_id=? OR nis=?) AND role="member"', [$identifier, $identifier]);
    $book = one('SELECT * FROM books WHERE book_id=?', [trim($_POST['book_id'] ?? '')]);

    if (!$member) {
        flash('Data siswa tidak ditemukan.', 'error');
    } elseif ($member['status'] === 'blocked') {
        flash('Akun siswa sedang diblokir.', 'error');
    } elseif (!$book) {
        flash('Buku tidak ditemukan.', 'error');
    } elseif ((int)$book['available'] < 1) {
        flash('Buku tidak tersedia untuk dipinjam.', 'error');
    } elseif (count_of('fines', 'user_id=? AND status="unpaid"', [$member['id']])) {
        flash('Siswa masih memiliki denda yang belum lunas.', 'error');
    } elseif (count_of('borrowings', 'user_id=? AND status IN ("active","overdue")', [$member['id']]) >= 3) {
        flash('Batas peminjaman maksimal 3 buku untuk setiap siswa.', 'error');
    } else {
        $dueDate = date('Y-m-d H:i:s', strtotime('+7 days 17:00:00'));
        execute(
            'INSERT INTO borrowings (user_id, book_id, due_date, status, fulfillment_method, delivery_address) VALUES (?,?,?,"active","sekolah",?)',
            [$member['id'], $book['id'], $dueDate, $member['address']]
        );
        execute('UPDATE books SET available=available-1 WHERE id=?', [$book['id']]);
        flash('Peminjaman buku berhasil dicatat untuk siswa ' . e($member['name']) . ' (' . e($member['kelas'] ?? 'SMA GLITZ') . ').');
    }
    redirect('admin/borrowing.php');
}

$pickupUsers = rows("SELECT DISTINCT user_id FROM borrowings WHERE status='pickup_pending'");
foreach ($pickupUsers as $pickupUser) {
    expire_pickup_reservations((int)$pickupUser['user_id']);
}

$pickupRequests = rows("SELECT br.*,u.name,u.member_id,u.nis,u.kelas,b.title,b.book_id FROM borrowings br JOIN users u ON u.id=br.user_id JOIN books b ON b.id=br.book_id WHERE br.status='pickup_pending' ORDER BY br.pickup_deadline");

$filterMember = trim($_GET['member_id'] ?? '');
$filterBook = trim($_GET['book_id'] ?? '');

$query = 'SELECT br.*,u.name,u.member_id,u.nis,u.kelas,u.address,b.title,b.book_id FROM borrowings br JOIN users u ON u.id=br.user_id JOIN books b ON b.id=br.book_id';
$where = [];
$params = [];
if ($filterMember !== '') {
    $where[] = '(u.member_id LIKE ? OR u.nis LIKE ? OR u.name LIKE ?)';
    $needle = '%' . $filterMember . '%';
    $params[] = $needle;
    $params[] = $needle;
    $params[] = $needle;
}
if ($filterBook !== '') {
    $where[] = '(b.book_id LIKE ? OR b.title LIKE ?)';
    $needleBook = '%' . $filterBook . '%';
    $params[] = $needleBook;
    $params[] = $needleBook;
}
if ($where) {
    $query .= ' WHERE ' . implode(' AND ', $where);
}
$data = rows($query . ' ORDER BY br.borrowed_at DESC', $params);

foreach ($data as $row) {
    if ($row['status'] === 'active' && strtotime($row['due_date']) < time()) {
        execute('UPDATE borrowings SET status="overdue" WHERE id=?', [$row['id']]);
    }
}

$members = rows('SELECT member_id, nis, name, kelas, address, phone FROM users WHERE role="member" AND status="active" ORDER BY name');
$books = rows('SELECT book_id, title, available FROM books WHERE available>0 ORDER BY title');

page_head('Peminjaman Buku', true);
admin_heading('Peminjaman Buku', 'Pilih nama siswa, alamat dan identitas akan otomatis terdeteksi tanpa input manual');
?>
<section class="modal" style="display:block;">
    <h2 style="margin-top:0;font-size:18px;margin-bottom:14px;color:var(--text);">Catat Peminjaman Buku Siswa</h2>
    <form method="post" id="adminBorrowForm">
        <div class="form-grid">
            <div class="form-row">
                <label>Nama Siswa / NIS</label>
                <select name="member_id" id="studentSelect" required onchange="detectStudentInfo()">
                    <option value="">-- Pilih Siswa SMA GLITZ --</option>
                    <?php foreach ($members as $m): ?>
                        <option value="<?= e($m['member_id']) ?>"
                            data-nis="<?= e($m['nis'] ?? '-') ?>"
                            data-name="<?= e($m['name']) ?>"
                            data-kelas="<?= e($m['kelas'] ?? 'Siswa') ?>"
                            data-address="<?= e($m['address'] ?? 'Alamat terdaftar di database') ?>"
                            data-phone="<?= e($m['phone'] ?? '-') ?>">
                            <?= e($m['name']) ?> · Kelas: <?= e($m['kelas'] ?? '-') ?> (NIS: <?= e($m['nis'] ?? $m['member_id']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-row">
                <label>Buku yang Tersedia</label>
                <select name="book_id" required>
                    <option value="">-- Pilih Buku --</option>
                    <?php foreach ($books as $b): ?>
                        <option value="<?= e($b['book_id']) ?>">
                            <?= e($b['book_id']) ?> · <?= e($b['title']) ?> (<?= (int)$b['available'] ?> tersedia)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Box Deteksi Otomatis Data Siswa (Tampil saat nama siswa dipilih) -->
        <div id="detectedStudentBox" style="display:none;background:#f8faff;border:1px solid #dbeafe;border-radius:10px;padding:14px 18px;margin-bottom:18px;">
            <div style="font-size:12px;font-weight:700;color:#1e40af;margin-bottom:10px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #e2e8f0;padding-bottom:6px;">
                <span>DATA SISWA TERVERIFIKASI</span>
                <span style="font-size:11px;background:#10b98120;color:#059669;padding:2px 8px;border-radius:99px;font-weight:700;">SMA GLITZ</span>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;font-size:13px;">
                <div>
                    <span style="color:#64748b;font-size:11px;display:block;">NAMA SISWA</span>
                    <strong id="detName" style="color:#0f172a;font-size:14px;">-</strong>
                </div>
                <div>
                    <span style="color:#64748b;font-size:11px;display:block;">NIS &amp; KELAS</span>
                    <span id="detNisKelas" style="color:#2563eb;font-weight:700;">-</span>
                </div>
                <div>
                    <span style="color:#64748b;font-size:11px;display:block;">NO. TELEPON / WA</span>
                    <span id="detPhone" style="color:#334155;">-</span>
                </div>
            </div>
        </div>

        <button class="btn btn-primary" type="submit" style="padding:10px 20px;">
            Proses Peminjaman
        </button>
    </form>
</section>

<script>
function detectStudentInfo() {
    const sel = document.getElementById('studentSelect');
    const box = document.getElementById('detectedStudentBox');
    if (!sel || !box) return;
    const opt = sel.options[sel.selectedIndex];
    if (!opt || !opt.value) {
        box.style.display = 'none';
        return;
    }
    document.getElementById('detName').textContent = opt.getAttribute('data-name') || '-';
    document.getElementById('detNisKelas').textContent = 'NIS: ' + (opt.getAttribute('data-nis') || '-') + ' · Kelas: ' + (opt.getAttribute('data-kelas') || '-');
    document.getElementById('detPhone').textContent = opt.getAttribute('data-phone') || '-';
    box.style.display = 'block';
}
</script>

<?php if ($pickupRequests): ?>
<section class="card table-card" style="margin-bottom:24px;">
    <h2>Konfirmasi Ambil Buku Siswa</h2>
    <table>
        <thead>
            <tr>
                <th>Siswa</th>
                <th>Buku</th>
                <th>Batas Pengambilan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pickupRequests as $request): ?>
            <tr>
                <td>
                    <b><?= e($request['name']) ?></b>
                    <br><small><?= e($request['kelas'] ?? '-') ?> · NIS: <?= e($request['nis'] ?? $request['member_id']) ?></small>
                </td>
                <td>
                    <b><?= e($request['title']) ?></b>
                    <br><small><?= e($request['book_id']) ?></small>
                </td>
                <td><?= date_id($request['pickup_deadline'], true) ?></td>
                <td>
                    <form method="post">
                        <input type="hidden" name="confirm_pickup" value="<?= (int)$request['id'] ?>">
                        <button class="success">Konfirmasi Buku Diambil</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php endif; ?>

<form class="card admin-id-search" method="get" style="display:flex;gap:10px;margin-bottom:20px;">
    <input name="member_id" value="<?= e($filterMember) ?>" placeholder="Cari nama siswa / NIS...">
    <input name="book_id" value="<?= e($filterBook) ?>" placeholder="Cari judul / ID buku...">
    <button type="submit" class="btn btn-primary">Cari</button>
    <a class="button secondary btn btn-secondary" href="<?= e(url('admin/borrowing.php')) ?>">Reset</a>
</form>

<section class="card table-card">
    <h2 style="margin:0;padding:16px 20px;font-size:16px;">Daftar Peminjaman Buku Siswa</h2>
    <table>
        <thead>
            <tr>
                <th>Siswa & Kelas</th>
                <th>Alamat Siswa</th>
                <th>Buku</th>
                <th>Tanggal Pinjam</th>
                <th>Jatuh Tempo</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data as $row): ?>
            <tr>
                <td>
                    <b><?= e($row['name']) ?></b>
                    <br><small class="muted"><?= e($row['kelas'] ?? '-') ?> · NIS: <?= e($row['nis'] ?? $row['member_id']) ?></small>
                </td>
                <td style="max-width:220px;font-size:12px;color:var(--muted);">
                    <?= e($row['address'] ?? ($row['delivery_address'] ?? '-')) ?>
                </td>
                <td>
                    <b><?= e($row['title']) ?></b>
                    <br><small class="muted"><?= e($row['book_id']) ?></small>
                </td>
                <td><?= date_id($row['borrowed_at']) ?></td>
                <td><?= date_id($row['due_date']) ?></td>
                <td><?= status_badge($row['status']) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$data): ?>
            <tr>
                <td colspan="6" class="muted" style="text-align:center;padding:24px;">Belum ada riwayat peminjaman.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</section>
<?php page_foot(true); ?>
