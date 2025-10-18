# Bilet Satın Alma Platformu

Bu proje, PHP ve SQLite kullanılarak geliştirilmiş dinamik ve çok kullanıcılı bir otobüs bileti satış platformudur. Proje, farklı yetkilere sahip kullanıcı rolleri (Yolcu, Firma Admini, Sistem Admini) için özel paneller ve işlevler içerir.

## Özellikler

- **Ziyaretçi:**
  - [cite_start]Ana sayfada sefer arama ve listeleme [cite: 16]
  - [cite_start]Bilet almaya çalıştığında giriş sayfasına yönlendirilme [cite: 17]

- **Kullanıcı (Yolcu):**
  - [cite_start]Kayıt olma, giriş yapma ve oturum yönetimi [cite: 19]
  - [cite_start]Koltuk seçimi ve kupon kodu kullanarak bilet satın alma [cite: 35]
  - [cite_start]Sanal bakiye sistemi üzerinden ödeme yapma [cite: 20]
  - [cite_start]Satın alınan biletleri listeleme [cite: 21]
  - [cite_start]"Son 1 saat" kuralına uygun olarak bilet iptal etme ve para iadesi alma [cite: 23, 24]
  - [cite_start]Satın alınan biletleri PDF olarak indirme [cite: 21, 22]

- **Firma Admin:**
  - Kendisine atanmış olan firmaya özel yönetim paneli
  - [cite_start]Sadece kendi firmasına ait seferleri yönetme (Ekleme/Silme/Güncelleme) [cite: 26, 28]
  - [cite_start]Sadece kendi firmasına ait kuponları yönetme (Ekleme/Silme/Güncelleme) [cite: 29]

- **Admin (Sistem Yöneticisi):**
  - Tüm sistemi yönetmek için özel admin paneli
  - [cite_start]Sistemdeki tüm otobüs firmalarını yönetme (CRUD) [cite: 32]
  - [cite_start]Yeni "Firma Admin" kullanıcıları oluşturma, düzenleme ve bir firmaya atama [cite: 32]
  - [cite_start]Tüm firmalarda geçerli genel indirim kuponları yönetme (CRUD) [cite: 33]

## Kullanılan Teknolojiler

- [cite_start]**Backend:** PHP 8.2 [cite: 9]
- [cite_start]**Veritabanı:** SQLite [cite: 11]
- [cite_start]**Frontend:** HTML, CSS, Bootstrap 5 [cite: 10]
- [cite_start]**Paketleme:** Docker [cite: 50]

## Kurulum ve Çalıştırma

Projeyi çalıştırmak için iki yöntem bulunmaktadır. Docker ile kurulum önerilir.

### 1. Docker ile Kurulum (Önerilen)

Bu yöntem, gerekli tüm bağımlılıkları içeren bir ortamda projeyi en kolay şekilde başlatmanızı sağlar.

1.  **Projeyi klonlayın:**
    ```bash
    git clone [https://github.com/muhammedSeyrek/bilet-satin-alma.git](https://github.com/muhammedSeyrek/bilet-satin-alma.git)
    cd bilet-satin-alma
    ```
2.  **Veritabanını ve test verilerini oluşturun:**
    Bu komut, `purchasing_tickets.db` dosyasını, tüm tabloları ve test kullanıcılarını oluşturur.
    ```bash
    php setup.php
    ```
3.  **Docker image'ını build edin:**
    ```bash
    docker build -t bilet-satin-alma .
    ```
4.  **Docker container'ını çalıştırın:**
    Bu komut, veritabanı dosyasının kalıcı olmasını ve kod değişikliklerinin anında yansımasını sağlar.
    ```bash
    docker run -p 8080:80 -v "$(pwd):/var/www/html" bilet-satin-alma
    ```
5.  **Siteye erişin:**
    Tarayıcınızdan `http://localhost:8080` adresine gidin.

### 2. Lokal Kurulum (Docker Olmadan)

1.  Bilgisayarınızda PHP ve `php-sqlite3` eklentisinin kurulu olduğundan emin olun.
2.  Projeyi klonlayın ve klasörün içine girin.
3.  Veritabanını oluşturmak için `php setup.php` komutunu çalıştırın.
4.  PHP'nin dahili sunucusunu başlatın:
    ```bash
    php -S localhost:8000
    ```
5.  Tarayıcınızdan `http://localhost:8000` adresine gidin.

## Test Hesapları

Sistemi test etmek için aşağıdaki hazır kullanıcı hesaplarını kullanabilirsiniz.

- **Admin:**
  - **E-posta:** `admin@sistem.com`
  - **Şifre:** `admin123` *(veya en son belirlediğiniz şifre)*

- **Firma Admin:**
  - **E-posta:** `ali@kamilkoc.com`
  - **Şifre:** `sifre123`

- **User (Yolcu):**
  - Bu rol için `kayit.php` sayfasından dilediğiniz kadar yeni kullanıcı oluşturabilirsiniz.