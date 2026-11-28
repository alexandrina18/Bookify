<?php
require_once __DIR__ . '/../includes/layout.php';
require_admin();

$filter = trim($_GET['q'] ?? '');
$filterKelas = trim($_GET['kelas'] ?? '');

$sql = "SELECT u.*, 
        (SELECT COUNT(*) FROM borrowings b WHERE b.user_id = u.id AND b.status IN ('active', 'overdue')) AS active_borrowings,
        (SELECT COUNT(*) FROM fines f WHERE f.user_id = u.id AND f.status = 'unpaid') AS unpaid_fines
        FROM users u 
        WHERE u.role = 'member'";
$params = [];

if ($filter !== '') {
    $sql .= " AND (u.name LIKE ? OR u.member_id LIKE ? OR u.nis LIKE ? OR u.email LIKE ?)";
    $needle = '%' . $filter . '%';
    $params = array_merge($params, [$needle, $needle, $needle, $needle]);
}

if ($filterKelas !== '') {
    $sql .= " AND u.kelas = ?";
    $params[] = $filterKelas;
}

$sql .= " ORDER BY u.kelas ASC, u.name ASC";
$students = rows($sql, $params);

// Ambil daftar kelas untuk dropdown filter
$kelasList = rows("SELECT DISTINCT kelas FROM users WHERE role = 'member' AND kelas IS NOT NULL ORDER BY kelas");

page_head('Data Siswa SMA GLITZ', true);
admin_heading('Data Siswa SMA GLITZ', 'Direktori data master siswa untuk peminjaman buku (Read-only / CRUD ditiadakan)');
?>
<div class="card" style="margin-bottom:20px;border-left:4px solid #765238;background:#fffdfa;">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;">
        <div style="display:flex;align-items:center;gap:14px;">
            <img src="<?= e(url('assets/sma-glitz-logo.jpg')) ?>" alt="Logo SMA GLITZ" style="width:48px;height:48px;object-fit:contain;border-radius:10px;background:#fff;padding:3px;border:1px solid #e2e8f0;box-shadow:0 3px 8px rgba(0,0,0,0.08);flex-shrink:0;">
            <div>
                <h3 style="margin:0 0 4px 0;font-size:16px;color:#38291f;">Master Data Siswa SMA GLITZ</h3>
                <p class="muted" style="margin:0;font-size:13px;">
                    Direktori data lengkap siswa untuk keperluan peminjaman buku perpustakaan secara otomatis tanpa input alamat manual.
                </p>
            </div>
        </div>
        <span class="badge" style="background:#f5ede1;color:#765238;border:1px solid #d4c2aa;padding:6px 14px;font-size:12px;font-weight:700;">
            Total Terdaftar: <?= count($students) ?> Siswa
        </span>
    </div>
</div>

<form class="card admin-id-search" method="get" style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;">
    <input name="q" value="<?= e($filter) ?>" placeholder="Cari NIS, nama siswa, atau email..." style="flex:1;min-width:220px;">
    <select name="kelas" style="width:auto;min-width:160px;">
        <option value="">-- Semua Kelas --</option>
        <?php foreach ($kelasList as $k): ?>
            <option value="<?= e($k['kelas']) ?>" <?= ($filterKelas === $k['kelas'] ? 'selected' : '') ?>>
                <?= e($k['kelas']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-primary">Filter</button>
    <a class="button secondary btn btn-secondary" href="<?= e(url('admin/members.php')) ?>">Reset</a>
</form>

<section class="card table-card">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;">
        <h2 style="margin:0;font-size:16px;">Daftar Siswa SMA GLITZ</h2>
        <span class="muted" style="font-size:12px;">Mode: Lihat Data (Tanpa CRUD)</span>
    </div>
    <table>
        <thead>
            <tr>
                <th>NIS</th>
                <th>Nama Lengkap</th>
                <th>Kelas</th>
                <th>Alamat Terdaftar</th>
                <th>No. Telepon / WA</th>
                <th>Email</th>
                <th>Pinjaman Aktif</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $row): ?>
            <?php 
                $k = $row['kelas'] ?? '-';
                $badgeStyle = 'background:#e0f2fe;color:#0369a1;border:1px solid #bae6fd;';
                if (str_contains($k, 'XII')) {
                    $badgeStyle = 'background:#fef3c7;color:#92400e;border:1px solid #fde68a;';
                } elseif (str_contains($k, 'XI')) {
                    $badgeStyle = 'background:#e0e7ff;color:#3730a3;border:1px solid #c7d2fe;';
                } elseif (str_contains($k, 'X')) {
                    $badgeStyle = 'background:#dcfce7;color:#15803d;border:1px solid #86efac;';
                }
            ?>
            <tr>
                <td>
                    <strong style="color:#765238;font-size:13px;"><?= e($row['nis'] ?? $row['member_id']) ?></strong>
                    <br><small class="muted"><?= e($row['member_id']) ?></small>
                </td>
                <td>
                    <b style="color:#2c2018;font-size:13px;"><?= e($row['name']) ?></b>
                    <br><small class="muted"><?= e(($row['gender'] ?? '') === 'female' ? 'Perempuan' : 'Laki-laki') ?></small>
                </td>
                <td>
                    <span class="badge" style="<?= $badgeStyle ?>font-weight:700;font-size:11px;padding:3px 9px;border-radius:6px;display:inline-block;letter-spacing:0.3px;">
                        <?= e($k) ?>
                    </span>
                </td>
                <td style="max-width:240px;font-size:12px;color:#4a3f35;">
                    <?= e($row['address'] ?: 'Alamat belum diatur') ?>
                </td>
                <td style="color:#4a3f35;"><?= e($row['phone'] ?: '-') ?></td>
                <td><small class="muted"><?= e($row['email']) ?></small></td>
                <td>
                    <?php if ((int)$row['active_borrowings'] > 0): ?>
                        <span class="badge badge-pending"><?= (int)$row['active_borrowings'] ?> buku</span>
                    <?php else: ?>
                        <span class="muted" style="font-size:12px;">0 buku</span>
                    <?php endif; ?>
                    <?php if ((int)$row['unpaid_fines'] > 0): ?>
                        <br><small style="color:#dc2626;font-weight:600;">Ada Denda</small>
                    <?php endif; ?>
                </td>
                <td><?= status_badge($row['status']) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$students): ?>
            <tr>
                <td colspan="8" class="muted" style="text-align:center;padding:24px;">Tidak ada data siswa ditemukan.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</section>
<?php page_foot(true); ?>
