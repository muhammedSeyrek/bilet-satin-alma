<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.php");
    exit();
}
if (!isset($_GET['id'])) {
    header("Location: admin_paneli.php");
    exit();
}

$kupon_id_to_delete = $_GET['id'];

try {
    $pdo = new PDO('sqlite:purchasing_tickets.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "DELETE FROM Coupons WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$kupon_id_to_delete]);

    header("Location: admin_paneli.php");
    exit();
} catch (PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}
?>