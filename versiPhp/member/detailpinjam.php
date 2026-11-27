<?php
require_once __DIR__.'/../includes/member.php';
$user=require_login();if($user['role']==='admin')redirect('admin/dashboard.php');
expire_pickup_reservations((int)$user['id']);
expire_unpaid_delivery_requests((int)$user['id']);
$book=one('SELECT * FROM books WHERE id=?',[(int)($_GET['book']??$_POST['book_id']??0)]);
$pickupPreview=next_pickup_deadline();
$deliveryAddress=trim((string)($user['address']??''))?:'Jl. Pawiyatan Luhur No. 1, Semarang';
const DELIVERY_FEE=15000;
if($_SERVER['REQUEST_METHOD']==='POST'){
    $method=$_POST['fulfillment_method']??'';
    if(!$book||$book['available']<1){flash('Buku tidak tersedia untuk dipinjam.','error');redirect('member/katalog.php');}
    if(!in_array($method,['pickup','delivery'],true)){flash('Pilih metode penerimaan buku.','error');redirect('member/detailpinjam.php?book='.(int)$book['id']);}
    $now=new DateTimeImmutable('now',new DateTimeZone('Asia/Jakarta'));
    $pickupDeadline=next_pickup_deadline($now);$estimate=$now->modify('+2 days')->setTime(17,0);
    $paymentDeadline=$now->modify('+3 hours');
    $due=$estimate->modify('+7 days')->setTime(17,0);
    $status=$method==='pickup'?'pickup_pending':'awaiting_payment';
    $reference=$method==='delivery'?'BKP'.date('ymd').strtoupper(bin2hex(random_bytes(3))):null;
    pdo()->beginTransaction();
    try{
        execute('INSERT INTO borrowings(user_id,book_id,borrowed_at,due_date,status,fulfillment_method,pickup_deadline,shipping_fee,receipt_no,estimated_delivery,delivery_address,payment_deadline) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)',[$user['id'],$book['id'],$now->format('Y-m-d H:i:s'),$method==='pickup'?$pickupDeadline->format('Y-m-d H:i:s'):$due->format('Y-m-d H:i:s'),$status,$method,$method==='pickup'?$pickupDeadline->format('Y-m-d H:i:s'):null,$method==='delivery'?DELIVERY_FEE:0,$reference,$method==='delivery'?$estimate->format('Y-m-d H:i:s'):null,$method==='delivery'?$deliveryAddress:null,$method==='delivery'?$paymentDeadline->format('Y-m-d H:i:s'):null]);
        execute('UPDATE books SET available=available-1 WHERE id=? AND available>0',[$book['id']]);
        $borrowingId=(int)pdo()->lastInsertId();pdo()->commit();
    }catch(Throwable $error){pdo()->rollBack();throw $error;}
    if($method==='delivery')redirect('member/pembayaran.php?id='.$borrowingId);
    flash('Permintaan ambil langsung tercatat. Ambil buku sebelum batas waktu yang tertera.');redirect('member/peminjaman.php');
}
member_start('Detail Peminjaman',$user,'katalog.php');
?>
<section class="member-section detail-pinjam-form"><span class="member-kicker">PILIH METODE PENERIMAAN</span><h1>Detail peminjaman</h1>
<?php if($book):?><p><b><?=e($book['title'])?></b> · <?=e($book['book_id'])?></p><form method="post" id="fulfillment-form"><input type="hidden" name="book_id" value="<?=(int)$book['id']?>"><fieldset><legend>Bagaimana Anda menerima buku?</legend><label class="fulfillment-choice"><input type="radio" name="fulfillment_method" value="pickup" required><span><b>Ambil langsung di perpustakaan</b><small>Batas pengambilan: <?=date_id($pickupPreview->format('Y-m-d H:i:s'),true)?></small></span></label><label class="fulfillment-choice"><input type="radio" name="fulfillment_method" value="delivery" required><span><b>Antar ke alamat</b><small>Alamat: <?=e($deliveryAddress)?></small><small>Biaya ekspedisi: <?=rupiah(DELIVERY_FEE)?> · estimasi 2 hari</small></span></label></fieldset><button type="submit" <?=((int)$book['available']<1?'disabled':'')?>>Konfirmasi pilihan</button></form><?php else:?><p>Buku tidak ditemukan atau sudah tidak tersedia.</p><?php endif;?></section>
<?php member_end(); ?>


