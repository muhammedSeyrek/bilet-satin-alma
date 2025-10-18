<?php
session_start();

// Güvenlik 1: Sadece 'company' rolündeki adminler bu işlemi yapabilir.
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'company' || !isset($_SESSION['company_id'])) {
    header("Location: index.php");
    exit();
}

// Güvenlik 2: Silinecek bir ID belirtilmiş mi?
if (!isset($_GET['id'])) {
    header("Location: firma_paneli.php");
    exit();
}

$sefer_id_to_delete = $_GET['id'];
$company_id = $_SESSION['company_id'];

try {
    $pdo = new PDO('sqlite:purchasing_tickets.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Güvenlik 3: Firma Admin'in, sadece KENDİ firmasına ait bir seferi silebildiğinden emin ol.
    // Bu, en önemli güvenlik adımıdır. Başka bir firmanın admini, URL'yi değiştirerek sizin seferinizi silememeli.
    $sql = "DELETE FROM Trips WHERE id = ? AND company_id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$sefer_id_to_delete, $company_id]);

    // Silme işlemi bittikten sonra, kullanıcıyı sefer listesine geri yönlendir.
    header("Location: firma_paneli.php");
    exit();

} catch (PDOException $e) {
    // Bir hata olursa, hatayı göster ve işlemi durdur.
    die("Veritabanı hatası: " . $e->getMessage());
}
?>