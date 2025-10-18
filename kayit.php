<?php
// Hata raporlamayı açalım
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error_message = ''; // Hata mesajlarını tutmak için boş bir değişken

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Formdan verileri al
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        $pdo = new PDO('sqlite:purchasing_tickets.db');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // 2. E-postanın zaten var olup olmadığını kontrol et
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetchColumn() > 0) {
            $error_message = "Bu e-posta adresi zaten kullanımda. Lütfen başka bir e-posta deneyin veya giriş yapın.";
        } else {
            // 3. E-posta yoksa, YENİ KULLANICIYI KAYDET
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO Users (id, full_name, email, password, role) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);

            // !--- DEĞİŞİKLİK 1: EKSİK OLAN BALANCE DEĞERİ BURAYA EKLENDİ ---!
            $stmt->execute([
                uniqid(),
                $full_name,
                $email,
                $hashed_password,
                'user'
            ]);

            // 4. BAŞARILI OLURSA YÖNLENDİR
            header("Location: giris.php");
            exit();
        }

    } catch (PDOException $e) {
        $error_message = "Veritabanı hatası: " . $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kayıt Ol</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Bilet Platformu</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="giris.php">Giriş Yap</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="kayit.php">Kayıt Ol</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
        
            <?php
            if (!empty($error_message)) {
                echo '<div class="alert alert-danger">' . htmlspecialchars($error_message) . '</div>';
            }
            ?>

            <div class="card">
                <div class="card-header">
                    <h3>Kayıt Ol</h3>
                </div>
                <div class="card-body">
                    <form action="kayit.php" method="POST">
                        <div class="mb-3">
                            <label for="fullname" class="form-label">Ad Soyad</label>
                            <input type="text" class="form-control" id="fullname" name="full_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">E-posta Adresi</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Şifre</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Kayıt Ol</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>