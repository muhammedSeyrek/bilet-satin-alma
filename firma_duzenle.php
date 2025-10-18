<?php
session_start();
// Güvenlik Kontrolleri
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin' || !isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

$firma_id_to_edit = $_GET['id'];

try {
    $pdo = new PDO('sqlite:purchasing_tickets.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // --- UPDATE KODU ---
    // Eğer form gönderilmişse (POST metoduyla)
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $yeni_firma_adi = $_POST['firma_adi'];

        $sqlUpdate = "UPDATE Bus_Company SET name = ? WHERE id = ?";
        $stmt = $pdo->prepare($sqlUpdate);
        $stmt->execute([$yeni_firma_adi, $firma_id_to_edit]);

        header("Location: admin_paneli.php");
        exit();
    }

    // DÜZENLENECEK FİRMANIN BİLGİLERİNİ ÇEK
    $stmt = $pdo->prepare("SELECT * FROM Bus_Company WHERE id = ?");
    $stmt->execute([$firma_id_to_edit]);
    $firma = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$firma) {
        header("Location: admin_paneli.php");
        exit();
    }

} catch (PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}
?>
<!doctype html>
<html lang="tr">
<head>
    <title>Firma Düzenle - Admin Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-danger">
    </nav>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Firmayı Düzenle</h3>
                </div>
                <div class="card-body">
                    <form action="firma_duzenle.php?id=<?= htmlspecialchars($firma['id']) ?>" method="POST">
                        <div class="mb-3">
                            <label for="firma_adi" class="form-label">Firma Adı</label>
                            <input type="text" class="form-control" id="firma_adi" name="firma_adi" value="<?= htmlspecialchars($firma['name']) ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Değişiklikleri Kaydet</button>
                        <a href="admin_paneli.php" class="btn btn-secondary">İptal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>