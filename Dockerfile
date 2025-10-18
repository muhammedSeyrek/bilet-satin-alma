FROM php:8.2-apache

# --- YENİ EKLENEN SATIR ---
# Container'ın saat dilimini Türkiye olarak ayarla
ENV TZ=Europe/Istanbul
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone
# -------------------------

RUN apt-get update && apt-get install -y libsqlite3-dev
RUN docker-php-ext-install pdo pdo_sqlite
COPY . /var/www/html/
RUN chown www-data:www-data purchasing_tickets.db