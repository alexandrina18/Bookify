<?php
require_once __DIR__.'/../includes/member.php';
$user=require_login();if($user['role']==='admin')redirect('admin/dashboard.php');
expire_pickup_reservations((int)$user['id']);
expire_unpaid_delivery_requests((int)$user['id']);
$book=one('SELECT * FROM books WHERE id=?',[(int)($_GET['book']??$_POST['book_id']??0)]);
$pickupPreview=next_pickup_deadline();
$deliveryAddress=trim((string)($user['address']??''))?:'Jl. Pawiyatan Luhur No. 1, Semarang';
const DELIVERY_FEE=15000;
$semarangDistricts=['Candisari','Gajahmungkur','Gayamsari','Semarang Selatan','Semarang Tengah','Semarang Utara','Semarang Timur','Semarang Barat','Tembalang','Banyumanik','Genuk','Pedurungan','Candisari','Mijen','Ngaliyan','Gunungpati','Tugu','Poncokusumo'];
$semarangPostalCodes=['50111','50112','50113','50114','50115','50116','50117','50118','50119','50121','50122','50123','50124','50125','50126','50127','50128','50129','50131','50132','50133','50134','50135','50136','50137','50138','50141','50142','50143','50144','50145','50146','50147','50148','50149','50231','50232','50233','50234','50241','50242','50243','50244','50245','50246','50247','50251','50252','50253','50254','50255','50256','50257','50261','50262','50263','50264','50265','50266','50271','50272','50273','50274','50275','50276','50277','50311','50312','50313','50314','50315','50316','50317','50318','50319','50321','50322','50323','50324','50325','50326','50327','50328','50329','50341','50342','50343','50344','50345','50346','50347','50348','50349','50351','50352','50353','50354','50355','50356','50357','50358','50359','50361','50362','50363','50364','50365','50366','50367','50368','50369','50371','50372','50373','50374','50375','50376','50377','50378','50379','50411','50412','50413','50414','50415','50416','50417','50418','50419','50421','50422','50423','50424','50425','50426','50427','50428','50429','50431','50432','50433','50434','50435','50436','50437','50438','50439','50441','50442','50443','50444','50445','50446','50447','50448','50449','50451','50452','50453','50454','50455','50456','50457','50458','50459','50461','50462','50463','50464','50465','50466','50467','50468','50469','50471','50472','50473','50474','50475','50476','50477','50478','50479','50511','50512','50513','50514','50515','50516','50517','50518','50519','50521','50522','50523','50524','50525','50526','50527','50528','50529','50531','50532','50533','50534','50535','50536','50537','50538','50539','50541','50542','50543','50544','50545','50546','50547','50548','50549','50551','50552','50553','50554','50555','50556','50557','50558','50559','50561','50562','50563','50564','50565','50566','50567','50568','50569','50571','50572','50573','50574','50575','50576','50577','50578','50579'];
if($_SERVER['REQUEST_METHOD']==='POST'){
    $method=$_POST['fulfillment_method']??'';
    $address=trim($_POST['delivery_address']??'');
    if(!$book||$book['available']<1){flash('Buku tidak tersedia untuk dipinjam.','error');redirect('member/katalog.php');}
    if(!in_array($method,['pickup','delivery'],true)){flash('Pilih metode penerimaan buku.','error');redirect('member/detailpinjam.php?book='.(int)$book['id']);}
    if($method==='delivery'){
        $addressValid=false;
        $addressUpper=strtoupper($address);
        foreach($semarangDistricts as $district){if(stripos($addressUpper,strtoupper($district))!==false){$addressValid=true;break;}}
        if(!$addressValid){preg_match_all('/\b\d{5}\b/',$address,$matches);if(!empty($matches[0])){foreach($matches[0] as $postalCode){if(in_array($postalCode,$semarangPostalCodes,true)){$addressValid=true;break;}}}}
        if(!$addressValid){flash('Maaf, layanan pengantaran hanya tersedia untuk wilayah Kota Semarang. Silakan pilih metode Ambil Langsung.','error');redirect('member/detailpinjam.php?book='.(int)$book['id']);}
        $deliveryAddress=$address;
    }
    $now=new DateTimeImmutable('now',new DateTimeZone('Asia/Jakarta'));
    $pickupDeadline=next_pickup_deadline($now);$estimate=$now->modify('+2 days')->setTime(17,0);
    $paymentDeadline=$now->modify('+3 hours');
    $due=$estimate->modify('+7 days')->setTime(17,0);
    $status=$method==='pickup'?'pickup_pending':'awaiting_payment';
    $reference=$method==='delivery'?'BKP'.date('ymd').strtoupper(bin2hex(random_bytes(3))):null;
    pdo()->beginTransaction();
    try{
        execute('INSERT INTO borrowings(user_id,book_id,borrowed_at,due_date,status,fulfillment_method,pickup_deadline,shipping_fee,receipt_no,estimated_delivery,delivery_address,payment_deadline) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)',[$user['id'],$book['id'],$now->format('Y-m-d H:i:s'),$method==='pickup'?$pickupDeadline->format('Y-m-d H:i:s'):$due->format('Y-m-d H:i:s'),$status,$method,$method==='pickup'?$pickupDeadline->format('Y-m-d H:i:s'):null,$method==='delivery'?DELIVERY_FEE:0,$reference,$method==='delivery'?$estimate->format('Y-m-d H:i:s'):null,$method==='delivery'?$address:null,$method==='delivery'?$paymentDeadline->format('Y-m-d H:i:s'):null]);
        execute('UPDATE books SET available=available-1 WHERE id=? AND available>0',[$book['id']]);
        $borrowingId=(int)pdo()->lastInsertId();pdo()->commit();
    }catch(Throwable $error){pdo()->rollBack();throw $error;}
    if($method==='delivery')redirect('member/pembayaran.php?id='.$borrowingId);
    flash('Permintaan ambil langsung tercatat. Ambil buku sebelum batas waktu yang tertera.');redirect('member/peminjaman.php');
}
member_start('Detail Peminjaman',$user,'katalog.php');
?>
<section class="member-section detail-pinjam-form"><span class="member-kicker">PILIH METODE PENERIMAAN</span><h1>Detail peminjaman</h1>
<?php if($book):?><p><b><?=e($book['title'])?></b> · <?=e($book['book_id'])?></p><form method="post" id="fulfillment-form"><input type="hidden" name="book_id" value="<?=(int)$book['id']?>"><fieldset><legend>Bagaimana Anda menerima buku?</legend><label class="fulfillment-choice"><input type="radio" name="fulfillment_method" value="pickup" required onchange="toggleAddressForm()"><span><b>Ambil langsung di perpustakaan</b><small>Batas pengambilan: <?=date_id($pickupPreview->format('Y-m-d H:i:s'),true)?></small></span></label><label class="fulfillment-choice"><input type="radio" name="fulfillment_method" value="delivery" required onchange="toggleAddressForm()"><span><b>Antar ke alamat</b><small>Biaya ekspedisi: <?=rupiah(DELIVERY_FEE)?> · estimasi 2 hari</small></span></label></fieldset><div id="address-form" style="display:none;margin-top:15px;"><label for="delivery_address"><b>Alamat Pengiriman</b></label><small>Masukkan alamat lengkap dengan kecamatan atau kode pos Kota Semarang</small><textarea name="delivery_address" id="delivery_address" rows="3" placeholder="Contoh: Jl. Merdeka No. 1, Candisari, Semarang 50123"><?=e($deliveryAddress)?></textarea></div><button type="submit" <?=((int)$book['available']<1?'disabled':'')?> class="btn btn-primary">Konfirmasi pilihan</button></form><?php else:?><p>Buku tidak ditemukan atau sudah tidak tersedia.</p><?php endif;?></section>
<script>
function toggleAddressForm(){
    const deliveryRadio=document.querySelector('input[name="fulfillment_method"][value="delivery"]');
    const addressForm=document.getElementById('address-form');
    if(deliveryRadio&&addressForm){
        addressForm.style.display=deliveryRadio.checked?'block':'none';
        const addressInput=document.getElementById('delivery_address');
        if(addressInput)addressInput.required=deliveryRadio.checked;
    }
}
document.addEventListener('DOMContentLoaded',toggleAddressForm);
</script>
<?php member_end(); ?>


