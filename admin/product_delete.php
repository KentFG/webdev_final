<?php
declare(strict_types=1);
require_once __DIR__ . '/../functions/helpers.php';
require_role('admin');
if($_SERVER['REQUEST_METHOD']!=='POST'||!verify_csrf($_POST['csrf_token']??null)){set_flash('error','Invalid request.');header('Location: dashboard.php');exit;}
$id=(int)($_POST['id']??0);$stmt=db()->prepare('UPDATE products SET is_active=0 WHERE id=?');$stmt->execute([$id]);set_flash('success','Product archived.');header('Location: dashboard.php');exit;
