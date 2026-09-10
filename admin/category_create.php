<?php
declare(strict_types=1);require_once __DIR__.'/../functions/helpers.php';require_role('admin');
if($_SERVER['REQUEST_METHOD']!=='POST'||!verify_csrf($_POST['csrf_token']??null)){set_flash('error','Invalid request.');redirect('admin/dashboard.php');}
$name=clean_text((string)($_POST['name']??''));$slug=strtolower(preg_replace('/[^a-z0-9]+/','-',trim($name,' -'))??'');$desc=clean_text((string)($_POST['description']??''));
if($name===''||$slug===''){set_flash('error','Category name is required.');redirect('admin/dashboard.php');}
try{$stmt=db()->prepare('INSERT INTO categories (name,slug,description) VALUES (?,?,?)');$stmt->execute([$name,$slug,$desc]);set_flash('success','Category created.');}catch(Throwable $e){set_flash('error','Category name or slug already exists.');}redirect('admin/dashboard.php');
