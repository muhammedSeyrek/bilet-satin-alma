<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Güvenlik ve veri kontrolleri
if (!isset($_SESSION['user_id']) || !isset($_GET['bilet_id'])) {
    header("Location: giris.php");
    exit();
}

$bilet_id = $_GET['bilet_id'];
$user_id = $_SESSION['user_id'];
$message = '';
$is_success = false;
$page_title = 'Bilet İptal Sonucu';

try {
    $pdo = new PDO('sqlite:purchasing_tickets.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT Tickets.total_price, Trips.departure_time FROM Tickets 
                           JOIN Trips ON Tickets.trip_id = Trips.id
                           WHERE Tickets.id = ? AND Tickets.user_id = ? AND Tickets.status = 'active'");
    $stmt->execute([$bilet_id, $user_id]);
    $bilet = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$bilet) {
        throw new Exception("Geçersiz bilet ID'si, bilet size ait değil veya zaten iptal edilmiş.");
    }

    // --- DOĞRU ZAMAN KONTROLÜ ---
    $kalkis_timestamp = strtotime($bilet['departure_time']);
    $simdiki_timestamp = time();
    $saniye_farki = $kalkis_timestamp - $simdiki_timestamp;
    
    if ($saniye_farki < 3600) { // 3600 saniye = 1 saat
        $is_success = false;
        throw new Exception("Seferin kalkış saati geçtiği veya kalkışa 1 saatten az bir süre kaldığı için biletinizi iptal edemezsiniz.");
    } else {
        // İptal işlemi yapılabilir
        $stmt = $pdo->prepare("SELECT balance FROM Users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $yeni_bakiye = $user['balance'] + $bilet['total_price'];

        $pdo->prepare("UPDATE Users SET balance = ? WHERE id = ?")->execute([$yeni_bakiye, $user_id]);
        $pdo->prepare("UPDATE Tickets SET status = 'canceled' WHERE id = ?")->execute([$bilet_id]);

        $is_success = true;
        $message = "Biletiniz başarıyla iptal edilmiştir.<br>";
        $message .= "İade Edilen Tutar: <strong>" . htmlspecialchars($bilet['total_price']) . " TL</strong><br>";
        $message .= "Yeni Bakiyeniz: <strong>" . htmlspecialchars($yeni_bakiye) . " TL</strong>";
    }

} catch (Exception $e) {
    $is_success = false;
    $message = "Bir hata oluştu: " . $e->getMessage();
}
?>
<!doctype html>
<html lang="tr">
<head>
    <title><?= $page_title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    </nav>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="alert <?= $is_success ? 'alert-success' : 'alert-danger' ?>" role="alert">
                <h4 class="alert-heading"><?= $is_success ? 'İşlem Başarılı!' : 'İşlem Başarısız!' ?></h4>
                <p><?= $message ?></p>
                <hr>
                <a href="biletlerim.php" class="btn btn-primary">Biletlerime Dön</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>