<?php
session_start();

// Güvenlik 1: Sadece 'admin' rolündeki kullanıcılar bu işlemi yapabilir.
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Güvenlik 2: Silinecek bir firma ID'si belirtilmiş mi?
if (!isset($_GET['id'])) {
    header("Location: admin_paneli.php");
    exit();
}

$firma_id_to_delete = $_GET['id'];

try {
    $pdo = new PDO('sqlite:purchasing_tickets.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL DELETE sorgusunu hazırla
    $sql = "DELETE FROM Bus_Company WHERE id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$firma_id_to_delete]);

    // Silme işlemi bittikten sonra, kullanıcıyı firma listesine geri yönlendir.
    header("Location: admin_paneli.php");
    exit();

} catch (PDOException $e) {
    // Bir hata olursa, hatayı göster ve işlemi durdur.
    // İleride bu hata yönetimi daha da geliştirilebilir (örn: bu firmaya ait seferler varsa silme).
    die("Veritabanı hatası: " . $e->getMessage());
}
?>