<?php
require_once __DIR__ . '/../includes/layout.php';
require_admin();

$config = [
 'books'=>['title'=>'Buku','table'=>'books','columns'=>['cover_url'=>'Sampul','book_id'=>'ID Buku','title'=>'Judul','author'=>'Pengarang','year'=>'Tahun','pages'=>'Halaman','genre'=>'Genre','stock'=>'Stok','available'=>'Tersedia'],'fields'=>['book_id'=>'text','title'=>'text','author'=>'text','cover_url'=>'text','year'=>'number','pages'=>'number','genre'=>'text','stock'=>'number','publisher_id'=>'select_publisher']],
 'publishers'=>['title'=>'Penerbit','table'=>'publishers','columns'=>['name'=>'Nama','location'=>'Lokasi'],'fields'=>['name'=>'text','location'=>'text']],
 'budget'=>['title'=>'Anggaran','table'=>'budgets','columns'=>['category'=>'Kategori','description'=>'Deskripsi','amount'=>'Jumlah','type'=>'Tipe','date'=>'Tanggal'],'fields'=>['category'=>'select_category','description'=>'textarea','amount'=>'number','type'=>'select_type','date'=>'date']],
];
if (!isset($resource) || !isset($config[$resource])) { http_response_code(404); exit('Halaman tidak ditemukan.'); }
$item = $config[$resource]; $db = pdo();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action=$_POST['action'] ?? '';
    try {
        if ($action === 'delete') { execute('DELETE FROM ' . $item['table'] . ' WHERE id=?', [(int)$_POST['id']]); flash('Data berhasil dihapus.'); }
        elseif ($action === 'create') {
            $columns=[];$placeholders=[];$values=[];
            foreach ($item['fields'] as $field=>$type) { $columns[]=$field; $placeholders[]='?'; $value=trim($_POST[$field] ?? ''); if (in_array($field,['year','pages','stock','amount','publisher_id'],true)) $value=$value===''?null:(int)$value; if ($field==='date' && $value==='') $value=date('Y-m-d'); $values[]=$value; }
            if ($resource==='books') { $columns[]='available';$placeholders[]='?';$values[]=(int)($_POST['stock'] ?? 0); }
            execute('INSERT INTO ' . $item['table'] . ' (' . implode(',',$columns) . ') VALUES (' . implode(',',$placeholders) . ')',$values); flash('Data baru berhasil ditambahkan.');
        }
    } catch (Throwable $error) { flash('Gagal menyimpan data. Pastikan data valid dan tidak duplikat.', 'error'); }
    redirect('admin/' . $resource . '.php');
}
$filterId=trim($_GET['id']??''); $listParams=[];
if ($resource==='books') { $listSql='SELECT b.*,p.name publisher FROM books b LEFT JOIN publishers p ON p.id=b.publisher_id'; if($filterId!==''){ $listSql.=' WHERE b.book_id LIKE ?'; $listParams[]='%'.$filterId.'%'; } $listSql.=' ORDER BY b.title'; }
else $listSql='SELECT * FROM '.$item['table'].' ORDER BY id DESC';
$data=rows($listSql,$listParams);
page_head($item['title'],true); admin_heading($item['title'],'Kelola data ' . strtolower($item['title']) . ' perpustakaan');
?>
<?php if ($resource==='books'): ?><form class="toolbar admin-id-search" method="get"><input name="id" value="<?= e($filterId) ?>" placeholder="Cari ID buku"><button type="submit" class="btn btn-primary">Cari</button><a class="button secondary btn btn-secondary" href="<?= e(url('admin/'.$resource.'.php')) ?>">Reset</a></form><?php endif; ?>
<details class="modal"><summary>+ Tambah <?= e(rtrim($item['title'],'s')) ?></summary><form method="post"><input type="hidden" name="action" value="create"><div class="form-grid"><?php foreach ($item['fields'] as $field=>$type): ?><div class="form-row"<?= $type==='textarea'?' style="grid-column:1/-1"':'' ?>><label><?= e(str_replace('_',' ',$field)) ?></label><?php if ($type==='textarea'): ?><textarea name="<?= e($field) ?>"></textarea><?php elseif ($type==='select_publisher'): ?><select name="publisher_id"><option value="">— Tanpa penerbit —</option><?php foreach(rows('SELECT id,name FROM publishers ORDER BY name') as $publisher): ?><option value="<?= $publisher['id'] ?>"><?= e($publisher['name']) ?></option><?php endforeach; ?></select><?php elseif ($type==='select_category'): ?><select name="category"><?php foreach(['salary','infrastructure','equipment','books','operations','loss','other'] as $value): ?><option value="<?= $value ?>"><?= $value ?></option><?php endforeach; ?></select><?php elseif ($type==='select_type'): ?><select name="type"><option value="expense">Pengeluaran</option><option value="income">Pemasukan</option></select><?php else: ?><input type="<?= e($type) ?>" name="<?= e($field) ?>" <?= in_array($field,['book_id','title','author','name','description','amount'],true)?'required':'' ?>><?php endif; ?></div><?php endforeach; ?></div><button class="btn btn-primary">Simpan</button></form></details>
<section class="card table-card"><table><thead><tr><?php foreach($item['columns'] as $label): ?><th><?= e($label) ?></th><?php endforeach; ?><th>Aksi</th></tr></thead><tbody><?php foreach($data as $row): ?><tr><?php foreach($item['columns'] as $field=>$label): ?><td><?php if($field==='cover_url'): ?><?php if(!empty($row['cover_url'])): ?><img src="<?= e(str_starts_with($row['cover_url'], 'http') ? $row['cover_url'] : url($row['cover_url'])) ?>" alt="Sampul" style="width:38px;height:52px;object-fit:cover;border-radius:4px;box-shadow:0 2px 5px rgba(0,0,0,0.15);"><?php else: ?>-<?php endif; ?><?php elseif($field==='amount'): ?><?= rupiah((int)$row[$field]) ?><?php elseif($field==='date'): ?><?= date_id($row[$field]) ?><?php else: ?><?= e($row[$field] ?? '-') ?><?php endif; ?></td><?php endforeach; ?><td><div class="actions"><form class="inline" method="post" data-confirm="Hapus data ini?"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $row['id'] ?>"><button class="danger">Hapus</button></form></div></td></tr><?php endforeach; ?><?php if(!$data): ?><tr><td colspan="<?= count($item['columns'])+1 ?>" class="muted">Belum ada data.</td></tr><?php endif; ?></tbody></table></section>
<?php page_foot(true); ?>
