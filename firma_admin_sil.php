<?php
session_start();

// Güvenlik 1: Sadece 'admin' rolündeki kullanıcılar bu işlemi yapabilir.
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Güvenlik 2: Silinecek bir kullanıcı ID'si belirtilmiş mi?
if (!isset($_GET['id'])) {
    header("Location: admin_paneli.php");
    exit();
}

$admin_id_to_delete = $_GET['id'];

try {
    $pdo = new PDO('sqlite:purchasing_tickets.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL DELETE sorgusunu hazırla. Sadece ID'si eşleşen ve rolü 'company' olan kullanıcıyı sil.
    // Bu, yanlışlıkla normal bir kullanıcıyı veya başka bir admini silmeyi önleyen ekstra bir güvenlik katmanıdır.
    $sql = "DELETE FROM Users WHERE id = ? AND role = 'company'";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$admin_id_to_delete]);

    // Silme işlemi bittikten sonra, kullanıcıyı admin paneline geri yönlendir.
    header("Location: admin_paneli.php");
    exit();

} catch (PDOException $e) {
    die("Veritabanı hatası: ". $e->getMessage());
}
?>