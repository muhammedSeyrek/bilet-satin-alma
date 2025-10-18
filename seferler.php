<?php
session_start();
// hata raporlama icin.
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ================================================================
// Parça 1: Formdan gelen bilgileri almak
// ================================================================
$kalkis_yeri = isset($_GET['kalkis']) ? $_GET['kalkis'] : '';
$varis_yeri = isset($_GET['varis']) ? $_GET['varis'] : '';

// ================================================================
// Parça 2: Veritabanına soru sormak
// ================================================================
$seferler = []; // Sonuçları tutmak için boş dizi oluştur
if (!empty($kalkis_yeri) && !empty($varis_yeri)) {
    $pdo = new PDO('sqlite:purchasing_tickets.db');
    $sql = "SELECT * FROM Trips WHERE departure_city = ? AND destination_city = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$kalkis_yeri, $varis_yeri]);
    $seferler = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sefer Sonuçları</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Bilet Platformu</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="#">Giriş Yap</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Kayıt Ol</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h3>Arama Sonuçları</h3>
    <hr>

    <?php
    // Parça 3: Sonuçları Ekranda Göstermek
    if (empty($seferler)) {
        // Eğer arama yapıldıysa ama sonuç bulunamadıysa bu mesajı göster
        if (!empty($kalkis_yeri)) {
            echo '<div class="alert alert-warning">Aradığınız kriterlere uygun sefer bulunamadı.</div>';
        }
    } else {
        // Sonuç geldiyse, her bir sefer için bir kart oluştur
        foreach ($seferler as $sefer) {
            echo '<div class="card mb-3">';
            echo '  <div class="card-body">';
            echo '      <h5 class="card-title">' . htmlspecialchars($sefer['departure_city']) . ' -> ' . htmlspecialchars($sefer['destination_city']) . '</h5>';
            echo '      <p class="card-text">';
            echo '          <strong>Firma:</strong> ' . htmlspecialchars($sefer['company_id']) . '<br>';
            echo '          <strong>Kalkış Saati:</strong> ' . htmlspecialchars($sefer['departure_time']) . '<br>';
            echo '          <strong>Fiyat:</strong> ' . htmlspecialchars($sefer['price']) . ' TL';
            echo '      </p>';
            if (isset($_SESSION['user_id']) && $_SESSION['user_role'] == 'user') {
                //                                                   ------> BU SATIRI KONTROL ET/DEĞİŞTİR <------
                echo '<a href="bilet_al.php?sefer_id=' . htmlspecialchars($sefer['id']) . '" class="btn btn-success">Bilet Al</a>';
            }
            echo '  </div>';
            echo '</div>';
        }
    }
    ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>