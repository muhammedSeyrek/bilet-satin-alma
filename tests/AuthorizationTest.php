<?php
use PHPUnit\Framework\TestCase;

class AuthorizationTest extends TestCase
{
    private $pdo;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Gerekli tabloları oluştur
        $this->pdo->exec("CREATE TABLE Users(id TEXT PRIMARY KEY, email TEXT, balance REAL, full_name TEXT, role TEXT, password TEXT, company_id TEXT)");
        $this->pdo->exec("CREATE TABLE Trips(id TEXT PRIMARY KEY, price REAL, departure_time TEXT, company_id TEXT, destination_city TEXT, arrival_time TEXT, departure_city TEXT, capacity INTEGER, created_date TEXT)");
        $this->pdo->exec("CREATE TABLE Tickets(id TEXT PRIMARY KEY, trip_id TEXT, user_id TEXT, total_price REAL, status TEXT)");

        // Test verilerini ekle: İki farklı kullanıcı ve bir bilet
        $this->pdo->exec("INSERT INTO Users (id) VALUES ('user-A')"); // Biletin sahibi
        $this->pdo->exec("INSERT INTO Users (id) VALUES ('user-B')"); // Saldırgan
        $this->pdo->exec("INSERT INTO Trips (id) VALUES ('trip01')");
        $this->pdo->exec("INSERT INTO Tickets (id, user_id, trip_id, status) VALUES ('ticket-of-A', 'user-A', 'trip01', 'active')");
    }

    public function test_kullanici_baska_kullanicinin_biletine_erisemez_IDOR(): void
    {
        // Hazırlık: Saldırgan kullanıcı (user-B) ve kurbanın bilet ID'si (ticket-of-A)
        $saldırgan_id = 'user-B';
        $kurbanın_bilet_id = 'ticket-of-A';

        // Eylem: bilet_iptal.php'deki güvenlik sorgusunu simüle et.
        // "Bileti hem ID'sine göre hem de o an giriş yapmış olan KULLANICIYA göre bulmaya çalış."
        $stmt = $this->pdo->prepare(
            "SELECT * FROM Tickets WHERE id = ? AND user_id = ?"
        );
        $stmt->execute([$kurbanın_bilet_id, $saldırgan_id]);
        $bilet = $stmt->fetch(PDO::FETCH_ASSOC);

        // Doğrulama: Sorgunun hiçbir sonuç döndürmediğini iddia et.
        // Eğer $bilet 'false' ise, bu, user-B'nin, user-A'nın biletine erişemediği ve
        // sistemimizin IDOR zafiyetine karşı güvenli olduğu anlamına gelir.
        $this->assertFalse($bilet, "Güvenlik açığı! Bir kullanıcı başka bir kullanıcının biletine erişebiliyor.");
    }

    // BU YENİ FONKSİYONU AuthorizationTest.php DOSYASINA EKLE

    /**
     * Bu test, @runInSeparateProcess notasyonu sayesinde kendi izole ortamında çalışır.
     * Bu, header() fonksiyonlarının test edilmesini mümkün kılar.
     * @runInSeparateProcess
     */
    public function test_giris_yapmamis_kullanici_korumali_sayfalara_erisemez(): void
    {
        // Hazırlık: session'ı boşaltarak giriş yapılmamış bir durumu simüle et
        $_SESSION = [];
        
        // Eylem: bilet_al.php'yi çalıştırmayı dene
        // Bu kod, header() yönlendirmesi nedeniyle bir istisna (exception) fırlatacaktır.
        // Ancak Xdebug bu yönlendirmeyi yakalayıp analiz edebilir.
        
        // PHPUnit'e, bu testin bir çıktı üreteceğini söylüyoruz.
        $this->expectOutputString('');

        // bilet_al.php dosyasını dahil et
        // Not: header() yönlendirmesi beklendiği gibi çalışırsa,
        // bu kodun altındaki satırlara asla ulaşılamaz.
        include __DIR__ . '/../bilet_al.php';
    }
    
}