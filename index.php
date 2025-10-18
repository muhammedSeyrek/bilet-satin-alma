<?php
$page_title = "Ana Sayfa";
require_once 'header.php';
require_once 'fonksiyonlar.php'; // Tarih formatlama fonksiyonu için

$kalkis_yeri = isset($_GET['kalkis']) ? $_GET['kalkis'] : '';
$varis_yeri = isset($_GET['varis']) ? $_GET['varis'] : '';
$seferler = [];

try {
    $pdo = new PDO('sqlite:purchasing_tickets.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT Trips.*, Bus_Company.name AS company_name 
            FROM Trips 
            JOIN Bus_Company ON Trips.company_id = Bus_Company.id";
    $params = [];
    if (!empty($kalkis_yeri) && !empty($varis_yeri)) {
        $sql .= " WHERE Trips.departure_city = ? AND Trips.destination_city = ?";
        $params[] = $kalkis_yeri;
        $params[] = $varis_yeri;
    }
    $sql .= " ORDER BY departure_time ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $tum_seferler = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($tum_seferler as $sefer) {
        if (strtotime($sefer['departure_time']) > time()) {
            $seferler[] = $sefer;
        }
    }
} catch (PDOException $e) {
    header("Location: hata.php?mesaj=Veritabani baglantisinda bir sorun olustu.");
    exit();
}
?>

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
                                <p class="card-text mb-1"><strong>Kalkış:</strong> <?= format_turkish_date($sefer['departure_time']) ?></p>
                                <p class="card-text"><strong>Firma:</strong> <?= htmlspecialchars($sefer['company_name']) ?></p>
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

<?php
require_once 'footer.php';
?>