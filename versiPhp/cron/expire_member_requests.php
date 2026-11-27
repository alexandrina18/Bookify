<?php
require_once __DIR__.'/../includes/member.php';
if(PHP_SAPI!=='cli'){http_response_code(403);exit("CLI only\n");}
$users=rows("SELECT DISTINCT user_id FROM borrowings WHERE status IN ('pickup_pending','awaiting_payment')");
foreach($users as $user){expire_pickup_reservations((int)$user['user_id']);expire_unpaid_delivery_requests((int)$user['user_id']);}
echo "Expired member requests processed: ".count($users)." account(s)\n";
