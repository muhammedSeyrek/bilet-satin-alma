# Bilet Satın Alma Platformu

Bu proje, PHP ve SQLite kullanılarak geliştirilmiş dinamik ve çok kullanıcılı bir otobüs bileti satış platformudur. Proje, farklı yetkilere sahip kullanıcı rolleri (Yolcu, Firma Admini, Sistem Admini) için özel paneller ve işlevler içerir.


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
    docker run -p 8080:80 -v bilet-satin-alma
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
  - **Şifre:** `admin`

- **Firma Admin:**
  - **E-posta:** `ali@kamilkoc.com`
  - **Şifre:** `sifre123`

- **User (Yolcu):**
  - Bu rol için `kayit.php` sayfasından dilediğiniz kadar yeni kullanıcı oluşturabilirsiniz.
