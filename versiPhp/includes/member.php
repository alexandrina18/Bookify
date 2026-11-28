<?php
require_once __DIR__ . '/layout.php';
function member_start(string $title, array $user, string $active): void {
    expire_pickup_reservations((int)$user['id']);
    expire_unpaid_delivery_requests((int)$user['id']);
    activate_estimated_deliveries((int)$user['id']);
    $links=['dashboard.php'=>'Dashboard','katalog.php'=>'Katalog','peminjaman.php'=>'Peminjaman','pelaporan.php'=>'Pelaporan','Riwayat.php'=>'Riwayat'];
    $avatar=!empty($user['photo_path'])?'<img src="'.e(url($user['photo_path'])).'" alt="Foto '.e($user['name']).'">':e(strtoupper(substr($user['name'],0,1)));
    page_head($title);
    $hasBookDetail = $active === 'katalog.php' && basename($_SERVER['PHP_SELF']??'') === 'katalog.php' && isset($_GET['book']);
    echo '<main class="member-app'.($hasBookDetail?' has-book-detail':'').'"><aside class="member-sidebar"><a class="member-brand" href="'.e(url('member/dashboard.php')).'"><img class="member-brand-logo" src="'.e(url('assets/sma-glitz-logo.jpg')) .'" alt="Logo SMA GLITZ"> SMA GLITZ</a><nav class="member-menu" aria-label="Navigasi siswa">';
    foreach($links as $file=>$label) echo '<a class="'.($active===$file?'selected':'').'" href="'.e(url('member/'.$file)).'">'.e($label).'</a>';
    echo '</nav><div class="member-menu-bottom"><details class="member-settings"><summary>Setting</summary><a href="'.e(url('member/cs.php')).'">Customer service</a><a href="'.e(url('member/profile.php')).'">Profile</a></details><a href="'.e(url('logout.php')).'">Keluar</a></div><div class="member-profile"><span class="profile-avatar">'.$avatar.'</span><span><b>'.e($user['name']).'</b><small>'.e($user['member_id']).'</small></span></div></aside><section class="member-content"><header class="member-topbar"><div class="member-greeting"><span class="profile-avatar small-avatar">'.$avatar.'</span><b>'.e($user['name']).'</b></div></header><div class="member-main">';
}
function member_end(?array $detail = null): void { echo '</div></section>'; if($detail) member_book_detail($detail); echo '</main>'; page_foot(); }
function member_book_card(array $book): void {
    $cover = !empty($book['cover_url']) ? (str_starts_with($book['cover_url'], 'http') ? $book['cover_url'] : url($book['cover_url'])) : '';
    echo '<a class="member-book-card member-book-link" href="'.e(url('member/katalog.php?book='.(int)$book['id'])).'"><div class="member-cover">';
    if($cover) echo '<img src="'.e($cover).'" alt="Sampul '.e($book['title']).'">'; else echo '<span class="cover-mark">SMA GLITZ<br><b>'.e(strtoupper(substr($book['title'],0,1))).'</b></span>';
    echo '</div><div class="member-book-info"><h3>'.e($book['title']).'</h3><p>'.e($book['author']).'</p><span class="member-availability '.((int)$book['available']>0?'in-stock':'out-stock').'">'.((int)$book['available']>0?'Tersedia':'Sedang dipinjam').'</span></div></a>';
}
function member_borrowing_badge(string $status): string {
    $labels=['active'=>'Ongoing','overdue'=>'Terlambat','returned'=>'Dikembalikan','pickup_pending'=>'Menunggu diambil','awaiting_payment'=>'Menunggu pembayaran','in_transit'=>'Dalam perjalanan','cancelled'=>'Dibatalkan'];
    return '<span class="badge badge-'.e($status).'">'.e($labels[$status]??ucfirst($status)).'</span>';
}

function member_book_detail(array $book): void {
    $rating=one('SELECT AVG(rating) average, COUNT(*) total FROM reviews WHERE book_id=?',[$book['id']]);
    $cover = !empty($book['cover_url']) ? (str_starts_with($book['cover_url'], 'http') ? $book['cover_url'] : url($book['cover_url'])) : '';
    echo '<aside class="member-feature-panel"><div class="feature-label">DETAIL BUKU</div><div class="feature-cover member-cover">';
    if($cover) echo '<img src="'.e($cover).'" alt="Sampul '.e($book['title']).'">'; else echo '<span class="cover-mark">SMA GLITZ<br><b>'.e(strtoupper(substr($book['title'],0,1))).'</b></span>';
    echo '</div><span class="feature-genre">'.e($book['genre']?:'Koleksi Bookify').'</span><h2>'.e($book['title']).'</h2><p class="feature-author">'.e($book['author']).'</p><div class="feature-rating"><span>★★★★★</span> <b>'.($rating['average']?number_format((float)$rating['average'],1):'—').'</b> ('.(int)$rating['total'].' review)</div><div class="feature-stats"><span><b>'.(int)$book['pages'].'</b>Halaman</span><span><b>'.(int)$book['available'].'</b>Tersedia</span><span><b>'.e($book['year']?:'—').'</b>Tahun</span></div><p class="feature-description">'.e($book['synopsis']??'Buku pilihan untuk menambah wawasan dan menemani waktu membaca Anda.').'</p><a class="feature-button" href="'.e(url('member/detailpinjam.php?book='.(int)$book['id'])).'">Pinjam</a></aside>';
}

function next_pickup_deadline(?DateTimeImmutable $from = null): DateTimeImmutable {
    $now=$from??new DateTimeImmutable('now',new DateTimeZone('Asia/Jakarta'));
    for($i=0;$i<8;$i++){
        $day=(int)$now->format('N');$close=($day===6?'13:00':'17:00');
        if($day<=6 && $now->format('H:i:s')<($close.':00')) return $now->setTime(...array_map('intval',explode(':',$close)));
        $now=$now->modify('+1 day')->setTime(8,0);
    }
    return $now->setTime(17,0);
}

function expire_pickup_reservations(int $userId): void {
    $pending=rows("SELECT id,book_id,pickup_deadline FROM borrowings WHERE user_id=? AND status='pickup_pending'",[$userId]);
    $expired=array_values(array_filter($pending,static fn($item)=>!empty($item['pickup_deadline'])&&strtotime($item['pickup_deadline'])<time()));
    if(!$expired)return;
    pdo()->beginTransaction();
    try{foreach($expired as $item){execute("UPDATE borrowings SET status='cancelled',updated_at=CURRENT_TIMESTAMP WHERE id=? AND status='pickup_pending'",[$item['id']]);execute('UPDATE books SET available=available+1 WHERE id=?',[$item['book_id']]);}pdo()->commit();}catch(Throwable $error){pdo()->rollBack();throw $error;}
}

function expire_unpaid_delivery_requests(int $userId): void {
    $pending=rows("SELECT id,book_id,payment_deadline FROM borrowings WHERE user_id=? AND status='awaiting_payment'",[$userId]);
    $expired=array_values(array_filter($pending,static fn($item)=>!empty($item['payment_deadline'])&&strtotime($item['payment_deadline'])<time()));
    if(!$expired)return;
    pdo()->beginTransaction();
    try{foreach($expired as $item){execute("UPDATE borrowings SET status='cancelled',updated_at=CURRENT_TIMESTAMP WHERE id=? AND status='awaiting_payment'",[$item['id']]);execute('UPDATE books SET available=available+1 WHERE id=?',[$item['book_id']]);}pdo()->commit();}catch(Throwable $error){pdo()->rollBack();throw $error;}
}

function activate_estimated_deliveries(int $userId): void {
    $items=rows("SELECT id,estimated_delivery FROM borrowings WHERE user_id=? AND fulfillment_method='delivery' AND status='in_transit'",[$userId]);
    foreach($items as $item){if(!empty($item['estimated_delivery'])&&strtotime($item['estimated_delivery'])<=time())execute("UPDATE borrowings SET status='active',borrowed_at=estimated_delivery,due_date=datetime(estimated_delivery,'+7 days','start of day','+17 hours'),updated_at=CURRENT_TIMESTAMP WHERE id=? AND status='in_transit'",[$item['id']]);}
}
