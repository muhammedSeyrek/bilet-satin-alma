<?php
// Oturumu her zaman en başta başlat
session_start();

// Hata raporlamayı açalım
ini_set('display_errors', 1);
error_reporting(E_ALL);

// --- YENİ EKLENEN PHP BÖLÜMÜ ---
$kalkis_yeri = isset($_GET['kalkis']) ? $_GET['kalkis'] : '';
$varis_yeri = isset($_GET['varis']) ? $_GET['varis'] : '';

try {
    $pdo = new PDO('sqlite:purchasing_tickets.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Temel SQL sorgusu: Sadece kalkış saati geçmemiş seferleri getirir
    $sql = "SELECT * FROM Trips WHERE departure_time > datetime('now', 'localtime')";
    $params = [];

    // Eğer arama yapılmışsa, WHERE koşullarını SQL'e ekle
    if (!empty($kalkis_yeri) && !empty($varis_yeri)) {
        $sql .= " AND departure_city = ? AND destination_city = ?";
        $params[] = $kalkis_yeri;
        $params[] = $varis_yeri;
    }
    
    $sql .= " ORDER BY departure_time ASC"; // Seferleri tarihe göre sırala

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $seferler = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}
// --- YENİ EKLENEN PHP BÖLÜMÜ BİTTİ ---
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bilet Satın Alma Platformu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Bilet Platformu</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item"><a class="nav-link" href="#">Hoş Geldin, <?= htmlspecialchars($_SESSION['user_fullname']); ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="biletlerim.php">Biletlerim</a></li>
                    <li class="nav-item"><a class="nav-link" href="cikis.php">Çıkış Yap</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="giris.php">Giriş Yap</a></li>
                    <li class="nav-item"><a class="nav-link" href="kayit.php">Kayıt Ol</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><h3>Sefer Arama</h3></div>
                <div class="card-body">
                    <form action="index.php" method="GET">
                        <div class="row">
                            <div class="col-md-5 mb-3">
                                <label for="kalkis" class="form-label">Kalkış Yeri</label>
                                <input type="text" class="form-control" id="kalkis" name="kalkis" value="<?= htmlspecialchars($kalkis_yeri) ?>">
                            </div>
                            <div class="col-md-5 mb-3">
                                <label for="varis" class="form-label">Varış Yeri</label>
                                <input type="text" class="form-control" id="varis" name="varis" value="<?= htmlspecialchars($varis_yeri) ?>">
                            </div>
                            <div class="col-md-2 d-grid mb-3">
                                <label class="form-label">&nbsp;</label> 
                                <button type="submit" class="btn btn-primary">Filtrele</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-12">
            <h4>Tüm Seferler</h4>
            <hr>
            <?php if (empty($seferler)): ?>
                <div class="alert alert-warning">Gösterilecek uygun sefer bulunamadı.</div>
            <?php else: ?>
                <?php foreach ($seferler as $sefer): ?>
                    <div class="card mb-3">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title"><?= htmlspecialchars($sefer['departure_city']) ?> -> <?= htmlspecialchars($sefer['destination_city']) ?></h5>
                                <p class="card-text mb-1">
                                    <strong>Kalkış:</strong> <?= htmlspecialchars($sefer['departure_time']) ?>
                                </p>
                                <p class="card-text">
                                    <strong>Firma:</strong> <?= htmlspecialchars($sefer['company_id']) ?>
                                </p>
                            </div>
                            <div class="text-end">
                                <h4 class="text-success"><?= htmlspecialchars($sefer['price']) ?> TL</h4>
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <a href="bilet_al.php?sefer_id=<?= $sefer['id'] ?>" class="btn btn-success">Bilet Al</a>
                                <?php else: ?>
                                    <a href="giris.php" class="btn btn-success">Bilet Almak İçin Giriş Yap</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>