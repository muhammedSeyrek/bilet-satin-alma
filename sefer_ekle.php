<?php
session_start();

// Güvenlik Kontrolü
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'company') {
    header("Location: index.php");
    exit();
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

// --- YENİ EKLENEN FORM İŞLEME KODU ---
// Eğer sayfaya form gönderilmişse (POST metoduyla)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Formdan gelen tüm verileri al
    $departure_city = $_POST['departure_city'];
    $destination_city = $_POST['destination_city'];
    $departure_time = $_POST['departure_time'];
    $arrival_time = $_POST['arrival_time'];
    $price = $_POST['price'];
    $capacity = $_POST['capacity'];
    
    // 2. Bu seferi hangi firmaya ekleyeceğimizi session'dan al
    $company_id = $_SESSION['company_id'];

    try {
        $pdo = new PDO('sqlite:purchasing_tickets.db');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // 3. Veritabanına yeni seferi ekle
        $sql = "INSERT INTO Trips (id, company_id, departure_city, destination_city, departure_time, arrival_time, price, capacity) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            uniqid('trip_'),
            $company_id,
            $departure_city,
            $destination_city,
            $departure_time,
            $arrival_time,
            $price,
            $capacity
        ]);

        // 4. İşlem başarılıysa, firma paneline geri yönlendir
        header("Location: firma_paneli.php");
        exit();

    } catch (PDOException $e) {
        // Bir hata olursa, ekranda göster (daha sonra bu hatalar daha güzel gösterilebilir)
        die("Veritabanı hatası: " . $e->getMessage());
    }
}
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Yeni Sefer Ekle - Firma Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="firma_paneli.php">Firma Paneli</a>
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
            <li class="nav-item"><a class="nav-link" href="#">Hoş Geldin, <?= htmlspecialchars($_SESSION['user_fullname']); ?></a></li>
            <li class="nav-item"><a class="nav-link" href="cikis.php">Çıkış Yap</a></li>
        </ul>
    </div>
</nav>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3>Yeni Sefer Ekle</h3>
                </div>
                <div class="card-body">
                    <form action="sefer_ekle.php" method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="departure_city" class="form-label">Kalkış Şehri</label>
                                <input type="text" class="form-control" id="departure_city" name="departure_city" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="destination_city" class="form-label">Varış Şehri</label>
                                <input type="text" class="form-control" id="destination_city" name="destination_city" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="departure_time" class="form-label">Kalkış Zamanı</label>
                                <input type="datetime-local" class="form-control" id="departure_time" name="departure_time" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="arrival_time" class="form-label">Varış Zamanı</label>
                                <input type="datetime-local" class="form-control" id="arrival_time" name="arrival_time" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label">Fiyat (TL)</label>
                                <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="capacity" class="form-label">Koltuk Kapasitesi</label>
                                <input type="number" class="form-control" id="capacity" name="capacity" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success">Seferi Kaydet</button>
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