<?php
// 1. ADIM: Mevcut oturumu bulmak ve başlatmak için BU KOMUT ZORUNLUDUR.
session_start();

// 2. ADIM: Tüm oturum değişkenlerini (user_id, user_fullname vb.) temizle.
// Bu işlem, oturum kutusunun içini boşaltır.
$_SESSION = array();

// 3. ADIM: Oturumu tamamen yok et.
// Bu işlem, sunucudaki oturum kutusunu ve tarayıcıdaki session cookie'sini geçersiz kılar.
session_destroy();

// 4. ADIM: Kullanıcıyı ana sayfaya yönlendir.
// Artık bir ziyaretçi olduğu için ana sayfada "Giriş Yap" linkini görecek.
header("Location: index.php");
exit();
?>