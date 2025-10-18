<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'company' || !isset($_SESSION['company_id'])) {
    header("Location: index.php");
    exit();
}
if (!isset($_GET['id'])) {
    header("Location: firma_paneli.php");
    exit();
}

$kupon_id_to_delete = $_GET['id'];
$company_id = $_SESSION['company_id'];

try {
    $pdo = new PDO('sqlite:purchasing_tickets.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Güvenlik: Sadece o firmaya ait olan kuponu sil.
    $sql = "DELETE FROM Coupons WHERE id = ? AND company_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$kupon_id_to_delete, $company_id]);

    header("Location: firma_paneli.php");
    exit();
} catch (PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}
?>