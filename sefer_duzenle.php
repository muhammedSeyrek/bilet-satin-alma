<?php
session_start();
// Güvenlik Kontrolleri
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'company' || !isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

$sefer_id_to_edit = $_GET['id'];
$company_id = $_SESSION['company_id'];

try {
    $pdo = new PDO('sqlite:purchasing_tickets.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // --- YENİ EKLENEN UPDATE KODU ---
    // Eğer form gönderilmişse (POST metoduyla)
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // 1. Formdan gelen yeni verileri al
        $departure_city = $_POST['departure_city'];
        $destination_city = $_POST['destination_city'];
        $departure_time = $_POST['departure_time'];
        $arrival_time = $_POST['arrival_time'];
        $price = $_POST['price'];
        $capacity = $_POST['capacity'];

        // 2. Veritabanını GÜNCELLE
        $sqlUpdate = "UPDATE Trips SET 
                        departure_city = ?, 
                        destination_city = ?, 
                        departure_time = ?, 
                        arrival_time = ?, 
                        price = ?, 
                        capacity = ? 
                      WHERE id = ? AND company_id = ?"; // Güvenlik için company_id kontrolü yine çok önemli!
        
        $stmt = $pdo->prepare($sqlUpdate);
        $stmt->execute([
            $departure_city,
            $destination_city,
            $departure_time,
            $arrival_time,
            $price,
            $capacity,
            $sefer_id_to_edit,
            $company_id
        ]);

        // 3. İşlem başarılıysa, firma paneline geri yönlendir
        header("Location: firma_paneli.php");
        exit();
    }
    // ------------------------------------

    // DÜZENLENECEK SEFERİN BİLGİLERİNİ ÇEK (Bu kod zaten vardı)
    $stmt = $pdo->prepare("SELECT * FROM Trips WHERE id = ? AND company_id = ?");
    $stmt->execute([$sefer_id_to_edit, $company_id]);
    $sefer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sefer) {
        header("Location: firma_paneli.php");
        exit();
    }

} catch (PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sefer Düzenle - Firma Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    </nav>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3>Seferi Düzenle</h3>
                </div>
                <div class="card-body">
                    <form action="sefer_duzenle.php?id=<?= htmlspecialchars($sefer['id']) ?>" method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="departure_city" class="form-label">Kalkış Şehri</label>
                                <input type="text" class="form-control" id="departure_city" name="departure_city" value="<?= htmlspecialchars($sefer['departure_city']) ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="destination_city" class="form-label">Varış Şehri</label>
                                <input type="text" class="form-control" id="destination_city" name="destination_city" value="<?= htmlspecialchars($sefer['destination_city']) ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="departure_time" class="form-label">Kalkış Zamanı</label>
                                <input type="datetime-local" class="form-control" id="departure_time" name="departure_time" value="<?= htmlspecialchars($sefer['departure_time']) ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="arrival_time" class="form-label">Varış Zamanı</label>
                                <input type="datetime-local" class="form-control" id="arrival_time" name="arrival_time" value="<?= htmlspecialchars($sefer['arrival_time']) ?>" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label">Fiyat (TL)</label>
                                <input type="number" class="form-control" id="price" name="price" step="0.01" value="<?= htmlspecialchars($sefer['price']) ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="capacity" class="form-label">Koltuk Kapasitesi</label>
                                <input type="number" class="form-control" id="capacity" name="capacity" value="<?= htmlspecialchars($sefer['capacity']) ?>" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Değişiklikleri Kaydet</button>
                        <a href="firma_paneli.php" class="btn btn-secondary">İptal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>