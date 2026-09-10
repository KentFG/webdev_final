<?php
declare(strict_types=1);
require_once __DIR__ . '/../functions/helpers.php';
require_role('customer');
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf($_POST['csrf_token'] ?? null)) { set_flash('error','Invalid request.'); redirect('customer/profile.php'); }
$data=[]; foreach(['name','phone','address_line1','address_line2','city','province','postal_code','country'] as $key) $data[$key]=clean_text((string)($_POST[$key]??''));
$errors=validate_profile($data);
if($errors){ $_SESSION['profile_errors']=$errors; $_SESSION['profile_old']=$data; redirect('customer/profile.php'); }
$pdo=db(); $pdo->beginTransaction();
try { $pdo->prepare('UPDATE users SET name=? WHERE id=?')->execute([$data['name'],$_SESSION['user_id']]); $stmt=$pdo->prepare('INSERT INTO profiles (user_id,phone,address_line1,address_line2,city,province,postal_code,country) VALUES (?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE phone=VALUES(phone),address_line1=VALUES(address_line1),address_line2=VALUES(address_line2),city=VALUES(city),province=VALUES(province),postal_code=VALUES(postal_code),country=VALUES(country)'); $stmt->execute([$_SESSION['user_id'],$data['phone'],$data['address_line1'],$data['address_line2'],$data['city'],$data['province'],$data['postal_code'],$data['country']]); $pdo->commit(); set_flash('success','Profile updated successfully.'); }
catch(Throwable $e){ if($pdo->inTransaction())$pdo->rollBack(); set_flash('error','Unable to save your profile right now.'); }
redirect('customer/profile.php');
