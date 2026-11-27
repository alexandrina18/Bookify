<?php
require_once __DIR__.'/../includes/member.php';$user=require_login();if($user['role']==='admin')redirect('admin/dashboard.php');
expire_unpaid_delivery_requests((int)$user['id']);
$id=(int)($_GET['id']??$_POST['id']??0);$item=one('SELECT br.*,b.title,b.book_id FROM borrowings br JOIN books b ON b.id=br.book_id WHERE br.id=? AND br.user_id=? AND br.fulfillment_method="delivery"',[$id,$user['id']]);
$step=$_GET['step']??'method';
$selectedMethod=$_GET['method']??'';
$selectedBank=$_GET['bank']??'';
$vaNumbers=['BCA'=>'8800123456789012','BRI'=>'8800987654321098','BNI'=>'8800567890123456','Mandiri'=>'8800345678901234','BSI'=>'8800789012345678'];
if($_SERVER['REQUEST_METHOD']==='POST'){
    if($_POST['action']==='select_method'){
        $method=$_POST['payment_method']??'';
        if(!in_array($method,['qris','virtual_account'],true)){flash('Pilih metode pembayaran.','error');redirect('member/pembayaran.php?id='.$id);}
        redirect('member/pembayaran.php?id='.$id.'&step=details&method='.$method);
    }
    if($_POST['action']==='select_bank'){
        $bank=$_POST['bank']??'';
        if(!isset($vaNumbers[$bank])){flash('Pilih bank yang valid.','error');redirect('member/pembayaran.php?id='.$id.'&step=details&method=virtual_account');}
        redirect('member/pembayaran.php?id='.$id.'&step=details&method=virtual_account&bank='.$bank);
    }
    if($_POST['action']==='simulate_payment' && $item && $item['status']==='awaiting_payment'){
        $method=$_POST['payment_method']??'';
        $bank=$_POST['bank']??'';
        $paidAt=(new DateTimeImmutable('now'))->format('Y-m-d H:i:s');
        execute("UPDATE borrowings SET payment_method=?,paid_at=?,status='in_transit' WHERE id=? AND user_id=? AND status='awaiting_payment'",[$method,$paidAt,$id,$user['id']]);
        flash('Pembayaran simulasi berhasil. Paket sedang disiapkan.');
        redirect('member/pelacakan.php?id='.$id);
    }
}
member_start('Pembayaran Peminjaman',$user,'peminjaman.php'); ?>
<section class="member-section"><span class="member-kicker">PEMBAYARAN PENGANTARAN</span>
<?php if($item&&$item['status']==='awaiting_payment'):?>
    <?php if($step==='method'):?>
        <h1>Pilih metode pembayaran</h1>
        <p><?=e($item['title'])?> · Biaya ekspedisi <b><?=rupiah((int)$item['shipping_fee'])?></b></p>
        <p>Selesaikan pembayaran sebelum <?=date_id($item['payment_deadline'],true)?>.</p>
        <form method="post"><input type="hidden" name="id" value="<?=$id?>"><input type="hidden" name="action" value="select_method"><label class="payment-choice"><input type="radio" name="payment_method" value="qris" required> <span><b>QRIS</b><small>Scan QRIS untuk pembayaran cepat</small></span></label><label class="payment-choice"><input type="radio" name="payment_method" value="virtual_account" required> <span><b>Bank Virtual Account</b><small>Pilih bank dan transfer ke VA</small></span></label><button type="submit" class="btn btn-primary">Lanjut</button></form>
    <?php elseif($step==='details'):?>
        <h1>Detail Pembayaran</h1>
        <p><?=e($item['title'])?> · Total: <b><?=rupiah((int)$item['shipping_fee'])?></b></p>
        <p>Selesaikan pembayaran sebelum <?=date_id($item['payment_deadline'],true)?>.</p>
        <?php if($selectedMethod==='qris'):?>
            <div class="payment-detail"><h2>QRIS</h2><p class="simulation-notice">⚠️ QRIS ini hanya untuk simulasi pembayaran Bookify</p><div class="qris-container"><img src="<?=e(url('assets/qris-simulasi.png'))?>" alt="QRIS Simulasi" class="qris-image"></div><p class="payment-amount">Nominal: <b><?=rupiah((int)$item['shipping_fee'])?></b></p><form method="post"><input type="hidden" name="id" value="<?=$id?>"><input type="hidden" name="action" value="simulate_payment"><input type="hidden" name="payment_method" value="qris"><button type="submit" class="btn btn-success btn-block">Sudah Bayar</button></form></div>
        <?php elseif($selectedMethod==='virtual_account'):?>
            <?php if(!$selectedBank):?>
                <div class="payment-detail"><h2>Pilih Bank</h2><form method="post"><input type="hidden" name="id" value="<?=$id?>"><input type="hidden" name="action" value="select_bank"><?php foreach($vaNumbers as $bank=>$va):?><label class="bank-choice"><input type="radio" name="bank" value="<?=e($bank)?>" required> <span><b><?=e($bank)?></b></span></label><?php endforeach;?><button type="submit" class="btn btn-primary">Lanjut</button></form></div>
            <?php else:?>
                <div class="payment-detail"><h2>Virtual Account <?=e($selectedBank)?></h2><p class="simulation-notice">⚠️ Nomor VA ini hanya untuk simulasi pembayaran Bookify</p><div class="va-details"><p><strong>Bank:</strong> <?=e($selectedBank)?></p><p><strong>Nomor VA:</strong> <span class="va-number" id="va-number"><?=e($vaNumbers[$selectedBank])?></span></p><p><strong>Penerima:</strong> Bookify Perpustakaan</p><p><strong>Nominal:</strong> <b><?=rupiah((int)$item['shipping_fee'])?></b></p><button type="button" onclick="copyVA()" class="btn btn-warning">Salin Nomor VA</button></div><form method="post"><input type="hidden" name="id" value="<?=$id?>"><input type="hidden" name="action" value="simulate_payment"><input type="hidden" name="payment_method" value="virtual_account"><input type="hidden" name="bank" value="<?=e($selectedBank)?>"><button type="submit" class="btn btn-success btn-block">Sudah Bayar</button></form></div>
                <script>
                function copyVA(){const vaNumber=document.getElementById('va-number');navigator.clipboard.writeText(vaNumber.textContent).then(()=>alert('Nomor VA berhasil disalin!'));}
                </script>
            <?php endif;?>
        <?php endif;?>
        <p><a href="<?=e(url('member/pembayaran.php?id='.$id.'&step=method'))?>">← Kembali ke pilihan metode</a></p>
    <?php endif;?>
<?php elseif($item&&$item['status']==='cancelled'):?><p>Batas pembayaran tiga jam sudah lewat. Permintaan dihapus dan stok buku dikembalikan.</p>
<?php elseif($item&&in_array($item['status'],['in_transit','active'],true)):?><p>Pembayaran sudah tercatat. <a href="<?=e(url('member/pelacakan.php?id='.$id))?>">Lihat pelacakan paket</a>.</p>
<?php else:?><p>Transaksi tidak ditemukan.</p><?php endif;?></section>
<style>
.simulation-notice{background:#fff3cd;color:#856404;padding:10px;border-radius:4px;margin:10px 0}.qris-container{text-align:center;margin:20px 0}.qris-image{max-width:250px;height:auto}.payment-amount{font-size:18px;margin:15px 0}.va-details{background:#f8f9fa;padding:15px;border-radius:8px;margin:15px 0}.va-number{font-family:monospace;font-size:18px;background:#fff;padding:5px 10px;border:1px solid #ddd;border-radius:4px}.payment-detail{margin-top:20px;padding:20px;border:1px solid #e0e0e0;border-radius:8px}
</style>
<?php member_end(); ?>
