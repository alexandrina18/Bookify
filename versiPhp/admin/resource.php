<?php
require_once __DIR__ . '/../includes/layout.php';
require_admin();

$config = [
 'books'=>['title'=>'Buku','table'=>'books','columns'=>['book_id'=>'ID Buku','title'=>'Judul','author'=>'Pengarang','year'=>'Tahun','pages'=>'Halaman','genre'=>'Genre','stock'=>'Stok','available'=>'Tersedia'],'fields'=>['book_id'=>'text','title'=>'text','author'=>'text','year'=>'number','pages'=>'number','genre'=>'text','stock'=>'number','publisher_id'=>'select_publisher']],
 'members'=>['title'=>'Anggota','table'=>'users','columns'=>['member_id'=>'ID Anggota','name'=>'Nama','email'=>'Email','phone'=>'Telepon','gender'=>'Gender','status'=>'Status'],'fields'=>['member_id'=>'text','name'=>'text','email'=>'email','phone'=>'text','gender'=>'select_gender','address'=>'textarea','password'=>'password']],
 'publishers'=>['title'=>'Penerbit','table'=>'publishers','columns'=>['name'=>'Nama','location'=>'Lokasi'],'fields'=>['name'=>'text','location'=>'text']],
 'budget'=>['title'=>'Anggaran','table'=>'budgets','columns'=>['category'=>'Kategori','description'=>'Deskripsi','amount'=>'Jumlah','type'=>'Tipe','date'=>'Tanggal'],'fields'=>['category'=>'select_category','description'=>'textarea','amount'=>'number','type'=>'select_type','date'=>'date']],
];
if (!isset($resource) || !isset($config[$resource])) { http_response_code(404); exit('Halaman tidak ditemukan.'); }
$item = $config[$resource]; $db = pdo();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action=$_POST['action'] ?? '';
    try {
        if ($action === 'delete') { execute('DELETE FROM ' . $item['table'] . ' WHERE id=?', [(int)$_POST['id']]); flash('Data berhasil dihapus.'); }
        elseif ($action === 'status' && $resource === 'members') { execute('UPDATE users SET status=? WHERE id=? AND role="member"', [$_POST['status'],(int)$_POST['id']]); flash('Status anggota diperbarui.'); }
        elseif ($action === 'create') {
            $columns=[];$placeholders=[];$values=[];
            foreach ($item['fields'] as $field=>$type) { $columns[]=$field; $placeholders[]='?'; $value=trim($_POST[$field] ?? ''); if ($field==='password') $value=password_hash($value ?: 'member123', PASSWORD_DEFAULT); if (in_array($field,['year','pages','stock','amount','publisher_id'],true)) $value=$value===''?null:(int)$value; if ($field==='date' && $value==='') $value=date('Y-m-d'); $values[]=$value; }
            if ($resource==='books') { $columns[]='available';$placeholders[]='?';$values[]=(int)($_POST['stock'] ?? 0); }
            if ($resource==='members') { $columns[]='role';$placeholders[]='?';$values[]='member'; $columns[]='status';$placeholders[]='?';$values[]='active'; }
            execute('INSERT INTO ' . $item['table'] . ' (' . implode(',',$columns) . ') VALUES (' . implode(',',$placeholders) . ')',$values); flash('Data baru berhasil ditambahkan.');
        }
    } catch (Throwable $error) { flash('Gagal menyimpan data. Pastikan ID dan email tidak duplikat.', 'error'); }
    redirect('admin/' . $resource . '.php');
}
$listSql = $resource==='books' ? 'SELECT b.*,p.name publisher FROM books b LEFT JOIN publishers p ON p.id=b.publisher_id ORDER BY b.title' : ($resource==='members' ? "SELECT * FROM users WHERE role='member' ORDER BY member_id" : 'SELECT * FROM '.$item['table'].' ORDER BY id DESC');
$data=rows($listSql);
page_head($item['title'],true); admin_heading($item['title'],'Kelola data ' . strtolower($item['title']) . ' perpustakaan');
?>
<details class="modal"><summary>+ Tambah <?= e(rtrim($item['title'],'s')) ?></summary><form method="post"><input type="hidden" name="action" value="create"><div class="form-grid"><?php foreach ($item['fields'] as $field=>$type): ?><div class="form-row"<?= $type==='textarea'?' style="grid-column:1/-1"':'' ?>><label><?= e(str_replace('_',' ',$field)) ?></label><?php if ($type==='textarea'): ?><textarea name="<?= e($field) ?>"></textarea><?php elseif ($type==='select_publisher'): ?><select name="publisher_id"><option value="">— Tanpa penerbit —</option><?php foreach(rows('SELECT id,name FROM publishers ORDER BY name') as $publisher): ?><option value="<?= $publisher['id'] ?>"><?= e($publisher['name']) ?></option><?php endforeach; ?></select><?php elseif ($type==='select_gender'): ?><select name="gender"><option value="male">Laki-laki</option><option value="female">Perempuan</option></select><?php elseif ($type==='select_category'): ?><select name="category"><?php foreach(['salary','infrastructure','equipment','books','operations','loss','other'] as $value): ?><option value="<?= $value ?>"><?= $value ?></option><?php endforeach; ?></select><?php elseif ($type==='select_type'): ?><select name="type"><option value="expense">Pengeluaran</option><option value="income">Pemasukan</option></select><?php else: ?><input type="<?= e($type) ?>" name="<?= e($field) ?>" <?= $field==='password'?'placeholder="Default: member123"':'' ?> <?= in_array($field,['book_id','title','author','member_id','name','email','description','amount'],true)?'required':'' ?>><?php endif; ?></div><?php endforeach; ?></div><button>Simpan</button></form></details>
<section class="card table-card"><table><thead><tr><?php foreach($item['columns'] as $label): ?><th><?= e($label) ?></th><?php endforeach; ?><th>Aksi</th></tr></thead><tbody><?php foreach($data as $row): ?><tr><?php foreach($item['columns'] as $field=>$label): ?><td><?php if($field==='status'): ?><?= status_badge($row[$field]) ?><?php elseif($field==='amount'): ?><?= rupiah((int)$row[$field]) ?><?php elseif($field==='date'): ?><?= date_id($row[$field]) ?><?php else: ?><?= e($row[$field] ?? '-') ?><?php endif; ?></td><?php endforeach; ?><td><div class="actions"><?php if($resource==='members'): ?><form class="inline" method="post"><input type="hidden" name="action" value="status"><input type="hidden" name="id" value="<?= $row['id'] ?>"><input type="hidden" name="status" value="<?= $row['status']==='active'?'blocked':'active' ?>"><button class="warning"><?= $row['status']==='active'?'Blokir':'Aktifkan' ?></button></form><?php endif; ?><form class="inline" method="post" data-confirm="Hapus data ini?"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $row['id'] ?>"><button class="danger">Hapus</button></form></div></td></tr><?php endforeach; ?><?php if(!$data): ?><tr><td colspan="<?= count($item['columns'])+1 ?>" class="muted">Belum ada data.</td></tr><?php endif; ?></tbody></table></section>
<?php page_foot(true); ?>
