# Verwende ein offizielles PHP-Image mit Apache
FROM php:8.2-apache

# Installiere Systemabhängigkeiten für PHP-Erweiterungen und Composer
# git und unzip werden oft von Composer benötigt
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    libonig-dev \       # <-- HIER HINZUGEFÜGT (für mbstring)
    pkg-config \        # <-- HIER HINZUGEFÜGT (oft nützlich)
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
# Das * nach composer.lock ist, um den Build-Cache zu nutzen, falls nur PHP-Dateien geändert werden
COPY composer.json composer.lock* ./

# Installiere PHP-Abhängigkeiten (PHPMailer)
# --no-dev: keine Entwicklungsabhängigkeiten
# --optimize-autoloader: für bessere Performance
RUN composer install --no-dev --optimize-autoloader

# Kopiere den Rest der Anwendung (dein smtp_test.php Skript)
COPY smtp_test.php .

# Stelle sicher, dass Apache die Dateien lesen kann (optional, oft schon korrekt durch das Basisimage)
# RUN chown -R www-data:www-data /var/www/html

# Apache hört standardmäßig auf Port 80, was bereits im Basisimage konfiguriert ist
# EXPOSE 80 (ist im Basisimage php:apache schon enthalten)

# Der CMD des Basisimages startet Apache, also müssen wir hier nichts weiter tun
