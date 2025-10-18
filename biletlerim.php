<?php
// Oturumu her zaman en başta başlat
session_start();

// GÜVENLİK KONTROLÜ: Kullanıcı giriş yapmamışsa, giriş sayfasına yönlendir.
if (!isset($_SESSION['user_id'])) {
    header("Location: giris.php");
    exit();
}

// Hata raporlamayı aç
ini_set('display_errors', 1);
error_reporting(E_ALL);

$user_id = $_SESSION['user_id'];
$biletler = []; // Biletleri tutacağımız boş dizi

try {
    $pdo = new PDO('sqlite:purchasing_tickets.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Veritabanından biletleri çekmek için SQL sorgusu
    // JOIN kullanarak Tickets ve Trips tablolarını birleştiriyoruz
    $sql = "SELECT 
                Tickets.id AS bilet_id,
                Trips.departure_city,
                Trips.destination_city,
                Trips.departure_time,
                Tickets.total_price
            FROM Tickets
            JOIN Trips ON Tickets.trip_id = Trips.id
            WHERE Tickets.user_id = ? AND Tickets.status = 'active'";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $biletler = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Biletlerim</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Bilet Platformu</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="#">Hoş Geldin, <?= htmlspecialchars($_SESSION['user_fullname']); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="biletlerim.php">Biletlerim</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="cikis.php">Çıkış Yap</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Satın Aldığım Biletler</h3>
        <a href="index.php" class="btn btn-secondary">Ana Sayfaya Dön</a>
    </div>
    <hr>
    
    <?php if (empty($biletler)): ?>
        <div class="alert alert-info">Henüz satın alınmış biletiniz bulunmamaktadır.</div>
    <?php else: ?>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Güzergah</th>
                    <th>Kalkış Zamanı</th>
                    <th>Fiyat</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($biletler as $bilet): ?>
                    <tr>
                        <td><?= htmlspecialchars($bilet['departure_city']) ?> -> <?= htmlspecialchars($bilet['destination_city']) ?></td>
                        <td><?= htmlspecialchars($bilet['departure_time']) ?></td>
                        <td><?= htmlspecialchars($bilet['total_price']) ?> TL</td>
                        <td>
                            <a href="bilet_iptal.php?bilet_id=<?= htmlspecialchars($bilet['bilet_id']) ?>" class="btn btn-danger btn-sm">İptal Et</a>
                            <a href="bilet_pdf.php?bilet_id=<?= htmlspecialchars($bilet['bilet_id']) ?>" class="btn btn-primary btn-sm" target="_blank">PDF İndir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>