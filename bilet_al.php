<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Güvenlik ve veri kontrolleri
if (!isset($_SESSION['user_id']) || !isset($_GET['sefer_id'])) {
    header("Location: index.php");
    exit();
}

$sefer_id = $_GET['sefer_id'];
$user_id = $_SESSION['user_id'];
$error_message = '';
$success_message = '';
$sefer = null;
$dolu_koltuklar = [];

// Sayfa ilk yüklendiğinde veya kupon uygulandığında önceki indirimleri sıfırla
$is_post_request = $_SERVER['REQUEST_METHOD'] === 'POST';
$is_coupon_apply = isset($_POST['kupon_uygula']);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    unset($_SESSION['indirim_orani']);
    unset($_SESSION['uygulanan_kupon']);
}

try {
    $pdo = new PDO('sqlite:purchasing_tickets.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Biletin zaten var olup olmadığını en başta kontrol et
    $stmt_check_ticket = $pdo->prepare("SELECT COUNT(*) FROM Tickets WHERE user_id = ? AND trip_id = ? AND status = 'active'");
    $stmt_check_ticket->execute([$user_id, $sefer_id]);
    if ($stmt_check_ticket->fetchColumn() > 0) {
        throw new Exception("Bu sefere ait zaten bir biletiniz bulunmaktadır.");
    }

    // Sefer ve dolu koltuk bilgilerini her zaman çek
    $stmt_sefer = $pdo->prepare("SELECT * FROM Trips WHERE id = ?");
    $stmt_sefer->execute([$sefer_id]);
    $sefer = $stmt_sefer->fetch(PDO::FETCH_ASSOC);

    if (!$sefer) throw new Exception("Sefer bulunamadı.");

    $sql_dolu_koltuklar = "SELECT T2.seat_number FROM Tickets AS T1 JOIN Booked_Seats AS T2 ON T1.id = T2.ticket_id WHERE T1.trip_id = ? AND T1.status = 'active'";
    $stmt_koltuklar = $pdo->prepare($sql_dolu_koltuklar);
    $stmt_koltuklar->execute([$sefer_id]);
    $dolu_koltuklar = array_column($stmt_koltuklar->fetchAll(PDO::FETCH_ASSOC), 'seat_number');

    if ($is_post_request) {
        if ($is_coupon_apply) {
            $kupon_kodu = trim($_POST['kupon_kodu']);
            if (empty($kupon_kodu)) throw new Exception("Lütfen bir kupon kodu girin.");

            $stmt_kupon = $pdo->prepare("SELECT * FROM Coupons WHERE code = ?");
            $stmt_kupon->execute([$kupon_kodu]);
            $kupon = $stmt_kupon->fetch(PDO::FETCH_ASSOC);

            if (!$kupon) throw new Exception("Geçersiz kupon kodu.");
            if (strtotime($kupon['expire_date']) < time()) throw new Exception("Bu kuponun süresi dolmuş.");
            if ($kupon['company_id'] !== null && $kupon['company_id'] !== $sefer['company_id']) throw new Exception("Bu kupon, bu firma için geçerli değildir.");
            
            $stmt_usage = $pdo->prepare("SELECT COUNT(*) FROM User_Coupons WHERE coupon_id = ?");
            $stmt_usage->execute([$kupon['id']]);
            if ($stmt_usage->fetchColumn() >= $kupon['usage_limit']) throw new Exception("Bu kupon kullanım limitine ulaşmış.");

            $_SESSION['indirim_orani'] = $kupon['discount'];
            $_SESSION['uygulanan_kupon'] = $kupon;
            $success_message = "Kupon başarıyla uygulandı!";

        } else { // Satın Almayı Onayla butonuna basıldıysa
            $secilen_koltuk = isset($_POST['koltuk']) ? (int)$_POST['koltuk'] : null;
            if(!$secilen_koltuk) throw new Exception("Lütfen bir koltuk seçiniz.");
            
            $bilet_fiyati = $sefer['price'];
            if (isset($_SESSION['indirim_orani'])) {
                $bilet_fiyati = $bilet_fiyati * (1 - $_SESSION['indirim_orani']);
            }
            
            $stmt_user = $pdo->prepare("SELECT balance FROM Users WHERE id = ?");
            $stmt_user->execute([$user_id]);
            $user = $stmt_user->fetch(PDO::FETCH_ASSOC);

            if(strtotime($sefer['departure_time']) < time()) throw new Exception("Kalkış saati geçmiş bir sefere bilet alamazsınız.");
            if($user['balance'] < $bilet_fiyati) throw new Exception("Bakiyeniz yetersiz.");
            if(in_array($secilen_koltuk, $dolu_koltuklar)) throw new Exception("Seçtiğiniz koltuk başkası tarafından alındı.");
            
            $yeni_bakiye = $user['balance'] - $bilet_fiyati;
            $bilet_id = uniqid('ticket_');
            
            $pdo->prepare("INSERT INTO Tickets (id, trip_id, user_id, total_price) VALUES (?, ?, ?, ?)")->execute([$bilet_id, $sefer_id, $user_id, $bilet_fiyati]);
            $pdo->prepare("INSERT INTO Booked_Seats (id, ticket_id, seat_number) VALUES (?, ?, ?)")->execute([uniqid('seat_'), $bilet_id, $secilen_koltuk]);
            $pdo->prepare("UPDATE Users SET balance = ? WHERE id = ?")->execute([$yeni_bakiye, $user_id]);
            
            if (isset($_SESSION['uygulanan_kupon'])) {
                $pdo->prepare("INSERT INTO User_Coupons (id, user_id, coupon_id) VALUES (?, ?, ?)")->execute([uniqid('uc_'), $user_id, $_SESSION['uygulanan_kupon']['id']]);
            }
            
            $success_message = "Biletiniz başarıyla oluşturulmuştur. Seçtiğiniz koltuk: <strong>{$secilen_koltuk}</strong>. Ödenen Tutar: <strong>" . round($bilet_fiyati, 2) . " TL</strong>. Kalan Bakiyeniz: <strong>" . round($yeni_bakiye, 2) . " TL</strong>";
            unset($_SESSION['indirim_orani'], $_SESSION['uygulanan_kupon']);
        }
    }

} catch (Exception $e) {
    $error_message = $e->getMessage();
}
?>
<!doctype html>
<html lang="tr">
<head>
    <title>Bilet Satın Alma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .koltuk-duzeni { display: flex; flex-direction: row; }
        .koltuk-sutunu { display: flex; flex-direction: column-reverse; margin-right: 15px; }
        .koltuk-koridor { height: 60px; }
        .koltuk { width: 50px; height: 50px; margin-bottom: 10px; border-radius: 5px; font-size: 1.1rem; }
        .koltuk.dolu { background-color: #dc3545; color: white; }
        .koltuk.bos { background-color: #198754; color: white; }
        .koltuk input { display: none; }
        .koltuk.secili { background-color: #ffc107; border: 2px solid #333; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Bilet Platformu</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Hoş Geldin, <?= htmlspecialchars($_SESSION['user_fullname']); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="biletlerim.php">Biletlerim</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cikis.php">Çıkış Yap</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<div class="container mt-5">
    <?php if ($error_message): ?>
        <div class="alert alert-danger"><p><?= htmlspecialchars($error_message) ?></p></div>
    <?php endif; ?>
    <?php if ($success_message && !$is_coupon_apply): ?>
        <div class="alert alert-success">
            <h4 class="alert-heading">İşlem Başarılı!</h4>
            <p><?= $success_message ?></p>
            <hr>
            <a href="index.php" class="btn btn-primary">Yeni Sefer Ara</a>
            <a href="biletlerim.php" class="btn btn-info">Biletlerimi Gör</a>
        </div>
    <?php else: ?>
        <form action="bilet_al.php?sefer_id=<?= htmlspecialchars($sefer_id) ?>" method="POST">
            <div class="row">
                <div class="col-md-5">
                    <h4>Sefer Bilgileri</h4>
                    <ul class="list-group">
                        <li class="list-group-item"><strong>Güzergah:</strong> <?= htmlspecialchars($sefer['departure_city']) ?> -> <?= htmlspecialchars($sefer['destination_city']) ?></li>
                        <li class="list-group-item"><strong>Kalkış:</strong> <?= htmlspecialchars($sefer['departure_time']) ?></li>
                        <li class="list-group-item">
                            <strong>Fiyat:</strong> 
                            <?php
                            // Gösterilecek fiyatı başta orijinal fiyat olarak ayarla
                            $gosterilecek_fiyat = $sefer['price'];

                            // Eğer session'da geçerli bir indirim oranı varsa
                            if (isset($_SESSION['indirim_orani'])) {
                                // İndirimli fiyatı hesapla
                                $indirimli_fiyat = $gosterilecek_fiyat * (1 - $_SESSION['indirim_orani']);
                                // Ekrana eski fiyatın üzerini çizip yeni fiyatı yazdır
                                echo '<del>' . htmlspecialchars($gosterilecek_fiyat) . ' TL</del> <strong class="text-success">' . round($indirimli_fiyat, 2) . ' TL</strong>';
                            } else {
                                // Eğer indirim yoksa, sadece orijinal fiyatı yazdır
                                echo htmlspecialchars($gosterilecek_fiyat) . ' TL';
                            }
                            ?>
                        </li>
                    </ul>

                    <div class="mt-3">
                        <label for="kupon_kodu" class="form-label">İndirim Kuponu</label>
                        
                        <?php if($success_message && $is_coupon_apply): ?>
                            <div class="alert alert-success py-2"><?= $success_message ?></div>
                        <?php endif; ?>
                        <div class="input-group">
                            <input type="text" class="form-control" name="kupon_kodu" placeholder="Kupon kodunu girin">
                            <button class="btn btn-outline-secondary" type="submit" name="kupon_uygula" value="1">Uygula</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-7">
                    <h4>Koltuk Seçimi</h4>
                    <div class="border p-3 bg-light">
                        <div class="koltuk-duzeni">
                            <?php
                            $koltuk_no = 1;
                            $sutun_sayisi = ceil($sefer['capacity'] / 4);
                            for ($sutun = 0; $sutun < $sutun_sayisi; $sutun++) {
                                echo '<div class="koltuk-sutunu">';
                                for ($sira = 1; $sira <= 4; $sira++) {
                                    if ($koltuk_no <= $sefer['capacity']) {
                                        $is_dolu = in_array($koltuk_no, $dolu_koltuklar);
                                        echo '<label class="koltuk btn ' . ($is_dolu ? 'dolu disabled' : 'bos') . '" for="koltuk-' . $koltuk_no . '">';
                                        echo $koltuk_no;
                                        if (!$is_dolu) { echo '<input type="radio" name="koltuk" id="koltuk-' . $koltuk_no . '" value="' . $koltuk_no . '">'; }
                                        echo '</label>';
                                    }
                                    if ($sira == 2) { echo '<div class="koltuk-koridor"></div>'; }
                                    $koltuk_no++;
                                }
                                echo '</div>';
                            }
                            ?>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary w-100">Satın Almayı Onayla</button>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>
<script>
    document.querySelectorAll('input[name="koltuk"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.koltuk.secili').forEach(label => label.classList.remove('secili'));
            if (this.checked) { this.parentElement.classList.add('secili'); }
        });
    });
</script>
</body>
</html>