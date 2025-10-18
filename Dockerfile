# Apache ve PHP 8'in kurulu olduğu hazır bir imajı temel alıyoruz
FROM php:8.2-apache

# Veritabanı bağlantısı için gerekli olan pdo ve pdo_sqlite eklentilerini kuruyoruz
# Tıpkı Kali'de "apt install php-sqlite3" yaptığımız gibi
RUN docker-php-ext-install pdo pdo_sqlite

# Proje dosyalarımızı (bu klasördeki her şeyi) Docker imajının içindeki
# web sunucusu klasörüne (/var/www/html) kopyalıyoruz
COPY . /var/www/html/