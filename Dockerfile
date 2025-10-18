# Apache ve PHP 8.2'nin kurulu olduğu imajı temel alıyoruz.
FROM php:8.2-apache

# --- YENİ EKLENEN SATIR ---
# PHP eklentilerini kurmadan önce, onların ihtiyaç duyduğu sistem kütüphanelerini kuruyoruz.
# apt-get update: Paket listesini günceller.
# apt-get install -y: Gerekli kütüphaneleri kurar (-y, "evet" demek için).
RUN apt-get update && apt-get install -y libsqlite3-dev

# Veritabanı bağlantısı için gerekli olan PHP eklentilerini kuruyoruz.
# Bu komut artık başarılı olacaktır çünkü bağımlılığı bir üst satırda kurduk.
RUN docker-php-ext-install pdo pdo_sqlite

# Proje dosyalarımızı Docker imajının içine kopyalıyoruz.
COPY . /var/www/html/