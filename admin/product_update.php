<?php
declare(strict_types=1);
require_once __DIR__ . '/../functions/helpers.php';
require_role('admin');
if($_SERVER['REQUEST_METHOD']!=='POST'||!verify_csrf($_POST['csrf_token']??null)){set_flash('error','Invalid request.');redirect('admin/dashboard.php');}
$id=(int)($_POST['id']??0);$name=clean_text((string)($_POST['name']??''));$description=clean_text((string)($_POST['description']??''));$price=clean_text((string)($_POST['price']??''));$stock=clean_text((string)($_POST['stock']??''));$image=clean_text((string)($_POST['image']??''));$categoryId=(string)($_POST['category_id']??'');$errors=validate_product($name,$description,$price,$stock,$image,$categoryId);
if($errors){$product=['id'=>$id,'name'=>$name,'description'=>$description,'price'=>$price,'stock'=>$stock,'image'=>$image,'category_id'=>$categoryId,'is_active'=>isset($_POST['is_active'])?1:0];require __DIR__.'/product_form.php';exit;}
$stmt=db()->prepare('UPDATE products SET category_id=?,name=?,description=?,price=?,stock=?,image=?,is_active=? WHERE id=?');$stmt->execute([(int)$categoryId,$name,$description,(float)$price,(int)$stock,$image,isset($_POST['is_active'])?1:0,$id]);set_flash('success','Product updated.');redirect('admin/dashboard.php');
