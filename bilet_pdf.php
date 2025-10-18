<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'fonksiyonlar.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['bilet_id'])) {
    die("Geçersiz istek.");
}

$bilet_id = $_GET['bilet_id'];
$user_id = $_SESSION['user_id'];

try {
    $pdo = new PDO('sqlite:purchasing_tickets.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT
                Tickets.id AS bilet_id, Tickets.total_price,
                Users.full_name AS yolcu_adi,
                Trips.departure_city, Trips.destination_city,
                Trips.departure_time, Trips.arrival_time,
                Bus_Company.name AS firma_adi
            FROM Tickets
            JOIN Users ON Tickets.user_id = Users.id
            JOIN Trips ON Tickets.trip_id = Trips.id
            JOIN Bus_Company ON Trips.company_id = Bus_Company.id
            WHERE Tickets.id = ? AND Tickets.user_id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$bilet_id, $user_id]);
    $bilet = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$bilet) {
        die("Hata: Bilet bulunamadı veya bu bilet size ait değil.");
    }

} catch (PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}

// tFPDF kütüphanesini projeye dahil et
require('tfpdf.php');
ob_clean(); 

// --- PDF OLUŞTURMA BÖLÜMÜ ---

$pdf = new tFPDF('P','mm','A4'); // P: Dikey, mm: milimetre, A4: sayfa boyutu
$pdf->AddPage();

// Türkçe karakterleri destekleyen bir font ekliyoruz.
// tFPDF ile gelen 'DejaVu' font ailesi bunun için idealdir.
$pdf->AddFont('DejaVu','','DejaVuSans.ttf',true);
$pdf->AddFont('DejaVu','B','DejaVuSans-Bold.ttf',true);

// Sayfa Kenar Boşlukları
$pdf->SetMargins(10, 10, 10);

// ---- Bilet Başlığı ----
$pdf->SetFont('DejaVu', 'B', 20);
$pdf->SetFillColor(230, 230, 230); // Açık gri arkaplan
$pdf->Cell(0, 15, 'ELEKTRONIK YOLCU BILETI', 1, 1, 'C', true); // 1: çerçeve, true: arkaplanı doldur
$pdf->Ln(10);

// ---- Bilet Detayları ----
$pdf->SetFont('DejaVu', 'B', 12);
$pdf->Cell(50, 8, 'Firma Adı:');
$pdf->SetFont('DejaVu', '', 12);
$pdf->Cell(0, 8, $bilet['firma_adi'], 0, 1);

$pdf->SetFont('DejaVu', 'B', 12);
$pdf->Cell(50, 8, 'Yolcu Adı Soyadı:');
$pdf->SetFont('DejaVu', '', 12);
$pdf->Cell(0, 8, $bilet['yolcu_adi'], 0, 1);

$pdf->SetFont('DejaVu', 'B', 12);
$pdf->Cell(50, 8, 'Bilet Numarası:');
$pdf->SetFont('DejaVu', '', 12);
$pdf->Cell(0, 8, $bilet['bilet_id'], 0, 1);
$pdf->Ln(8);

// ---- Sefer Detayları ----
$pdf->SetFont('DejaVu', 'B', 16);
$pdf->Cell(0, 10, 'Sefer Bilgileri', 'B', 1); // 'B': alt çizgi
$pdf->Ln(4);

$pdf->SetFont('DejaVu', 'B', 12);
$pdf->Cell(50, 8, 'Güzergah:');
$pdf->SetFont('DejaVu', '', 12);
$pdf->Cell(0, 8, $bilet['departure_city'] . ' -> ' . $bilet['destination_city'], 0, 1);

$pdf->SetFont('DejaVu', 'B', 12);
$pdf->Cell(50, 8, 'Kalkış Zamanı:');
$pdf->SetFont('DejaVu', '', 12);
$pdf->Cell(0, 8, format_turkish_date($bilet['departure_time']), 0, 1);

$pdf->SetFont('DejaVu', 'B', 12);
$pdf->Cell(50, 8, 'Tahmini Varış:');
$pdf->SetFont('DejaVu', '', 12);
$pdf->Cell(0, 8, format_turkish_date($bilet['arrival_time']), 0, 1);
$pdf->Ln(8);

// ---- Fiyat ----
$pdf->SetFont('DejaVu', 'B', 16);
$pdf->Cell(0, 10, 'Fiyat Bilgileri', 'B', 1);
$pdf->Ln(4);

$pdf->SetFont('DejaVu', 'B', 12);
$pdf->Cell(50, 8, 'Ödenen Tutar:');
$pdf->SetFont('DejaVu', 'B', 14); // Fiyatı daha büyük yapalım
$pdf->SetTextColor(0, 100, 0); // Koyu yeşil renk
$pdf->Cell(0, 8, $bilet['total_price'] . ' TL', 0, 1);


// PDF'i tarayıcıda göstermek için
$pdf->Output('I', 'bilet.pdf'); // 'I': Inline (tarayıcıda göster), 'D': Download (direkt indir)
exit;
?>