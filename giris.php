<?php
// OTURUM YÖNETİMİNİ BAŞLAT
// Bu komut her zaman sayfanın en başında olmalıdır.
session_start(); 

// Hata raporlamayı açalım
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
else {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ... (formdan veri alma ve veritabanı bağlantısı kodları burada aynı kalıyor)
    $email = $_POST['email'];
    $password = $_POST['password'];
    $error_message = '';

    try {
        $pdo = new PDO('sqlite:purchasing_tickets.db');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $pdo->prepare("SELECT * FROM Users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // GİRİŞ BAŞARILI, ŞİMDİ OTURUM BİLGİLERİNİ KAYDET
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_fullname'] = $user['full_name'];
            $_SESSION['user_role'] = $user['role'];
            
            // --- YENİ EKLENEN SATIR ---
            // Eğer rol 'company' ise, firma ID'sini de oturuma kaydet.
            if ($user['role'] === 'company') {
                $_SESSION['company_id'] = $user['company_id'];
                header("Location: firma_paneli.php");
                exit();
            }
            else if($user['role'] === 'admin') {
                $_SESSION['admin_id'] = $user['admin_id'];
                header("Location: admin_paneli.php");
                exit();
            }
            else{

                header("Location: index.php");
                exit();
            }
            

        } else {
            // GİRİŞ BAŞARISIZ
            $error_message = "E-posta veya şifre hatalı.";
        }
    } catch (PDOException $e) {
        $error_message = "Veritabanı hatası: " . $e->getMessage();
    }
}
}


?>


<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Giriş Yap</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Bilet Platformu</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active" href="giris.php">Giriş Yap</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="kayit.php">Kayıt Ol</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Giriş Yap</h3>
                </div>
                <div class="card-body">
                    <form action="giris.php" method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">E-posta Adresi</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Şifre</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-success">Giriş Yap</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>