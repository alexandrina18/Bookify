<?php
require_once __DIR__.'/../includes/member.php';$user=require_login();if($user['role']==='admin')redirect('admin/dashboard.php');
redirect('member/detailpinjam.php?book='.(int)($_GET['book']??0));
