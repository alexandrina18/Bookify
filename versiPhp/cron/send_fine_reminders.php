<?php
require_once __DIR__.'/../includes/bootstrap.php';
if(PHP_SAPI!=='cli'){http_response_code(403);exit("CLI only\n");}
$db=pdo();
$db->exec("INSERT OR IGNORE INTO fine_reminders(fine_id,user_id,next_reminder_at,ends_at) SELECT f.id,f.user_id,datetime(f.created_at,'+7 days'),datetime(f.created_at,'+60 days') FROM fines f WHERE f.status='unpaid'");
$due=rows("SELECT r.id reminder_id,r.fine_id,r.sent_count,r.ends_at,f.amount,u.name,u.email,b.title FROM fine_reminders r JOIN fines f ON f.id=r.fine_id JOIN users u ON u.id=r.user_id LEFT JOIN borrowings br ON br.id=f.borrowing_id LEFT JOIN books b ON b.id=br.book_id WHERE f.status='unpaid' AND r.next_reminder_at<=CURRENT_TIMESTAMP AND r.ends_at>=CURRENT_TIMESTAMP");
$from=getenv('BOOKIFY_MAIL_FROM')?:'noreply@bookify.local';$sent=0;
foreach($due as $reminder){
    if(!filter_var($reminder['email'],FILTER_VALIDATE_EMAIL))continue;
    $subject='Pengingat denda Bookify';
    $message="Halo {$reminder['name']},\n\nIni pengingat mingguan Bookify mengenai denda yang belum dibayar sebesar Rp ".number_format((int)$reminder['amount'],0,',','.')." untuk buku ".($reminder['title']?:'yang dipinjam').".\nPembayaran dilakukan langsung kepada admin perpustakaan sambil membawa buku.\n\nBookify Semarang";
    if(mail($reminder['email'],$subject,$message,"From: Bookify <{$from}>")){
        execute("UPDATE fine_reminders SET sent_count=sent_count+1,last_sent_at=CURRENT_TIMESTAMP,next_reminder_at=datetime(next_reminder_at,'+7 days') WHERE id=?",[$reminder['reminder_id']]);$sent++;
    }
}
echo "Fine reminder emails sent: {$sent}\n";
