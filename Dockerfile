# Verwende ein offizielles PHP-Image mit Apache
FROM php:8.2-apache

# Installiere Systemabhängigkeiten für PHP-Erweiterungen und Composer
# git und unzip werden oft von Composer benötigt
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    libonig-dev \
    pkg-config \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

# Installiere notwendige PHP-Erweiterungen
# zip für Composer, mbstring und xml werden von PHPMailer oder dessen Abhängigkeiten oft genutzt
RUN docker-php-ext-install zip mbstring xml curl

# Installiere Composer global
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Setze das Arbeitsverzeichnis
WORKDIR /var/www/html

# Kopiere composer.json und composer.lock (falls vorhanden)
COPY composer.json composer.lock* ./

# Installiere PHP-Abhängigkeiten (PHPMailer)
RUN composer install --no-dev --optimize-autoloader

# Kopiere den Rest der Anwendung (dein smtp_test.php Skript)
COPY smtp_test.php .

# (Rest des Dockerfiles bleibt gleich)
