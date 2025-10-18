<?php
$page_title = "Bir Hata Oluştu";
// Bu sayfa kendi başına bir header kullandığı için index'ten kopyaladık
require_once 'header.php';

// URL'den gelen hata mesajını al, eğer mesaj yoksa varsayılan bir metin göster
$hata_mesaji = isset($_GET['mesaj']) ? $_GET['mesaj'] : 'Beklenmedik bir sorun oluştu. Lütfen daha sonra tekrar deneyin.';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="alert alert-danger text-center">
                <h4 class="alert-heading">Üzgünüz, Bir Sorun Oluştu!</h4>
                <p><?= htmlspecialchars($hata_mesaji) ?></p>
                <hr>
                <a href="index.php" class="btn btn-primary">Ana Sayfaya Dön</a>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'footer.php';
?>