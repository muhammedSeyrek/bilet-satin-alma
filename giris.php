<?php
$page_title = "Giriş Yap";
require_once 'header.php';

// Eğer kullanıcı zaten giriş yapmışsa, onu ana sayfaya yönlendir
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error_message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    try {
        $pdo = new PDO('sqlite:purchasing_tickets.db');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $pdo->prepare("SELECT * FROM Users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_fullname'] = $user['full_name'];
            $_SESSION['user_role'] = $user['role'];
            
            if ($user['role'] === 'company') {
                $_SESSION['company_id'] = $user['company_id'];
                header("Location: firma_paneli.php");
                exit();
            } else if ($user['role'] === 'admin') {
                header("Location: admin_paneli.php");
                exit();
            } else {
                header("Location: index.php");
                exit();
            }
        } else {
            $error_message = "E-posta veya şifre hatalı.";
        }
    } catch (PDOException $e) {
        $error_message = "Veritabanı hatası: " . $e->getMessage();
    }
}
?>

<div class="container mt-5">
    <?php
    if (isset($_SESSION['flash_message'])) {
        echo '<div class="alert alert-success">' . $_SESSION['flash_message'] . '</div>';
        // Mesajı gösterdikten sonra, bir sonraki sayfa yenilemesinde tekrar görünmemesi için onu session'dan sil.
        unset($_SESSION['flash_message']);
    }
    ?>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>
            <div class="card">
                <div class="card-header"><h3>Giriş Yap</h3></div>
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

<?php
require_once 'footer.php';
?>