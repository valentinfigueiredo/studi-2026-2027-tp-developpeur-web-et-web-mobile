# Image de production : PHP 8.3 + Apache
FROM php:8.3-apache

# Extensions PHP : PDO MySQL, MongoDB, intl ; puis module de réécriture d'URL d'Apache
RUN apt-get update && apt-get install -y --no-install-recommends libssl-dev libicu-dev unzip git \
    && docker-php-ext-install pdo_mysql intl \
    # Extension MongoDB téléchargée depuis les releases GitHub officielles (version figée)
    && curl -fsSL -o /tmp/mongodb.tgz https://github.com/mongodb/mongo-php-driver/releases/download/1.20.1/mongodb-1.20.1.tgz \
    && pecl install /tmp/mongodb.tgz && docker-php-ext-enable mongodb && rm /tmp/mongodb.tgz \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

# Configuration PHP de production (pas d'affichage d'erreurs, limite d'upload)
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && { echo 'upload_max_filesize=2M'; echo 'post_max_size=8M'; echo 'expose_php=Off'; } > "$PHP_INI_DIR/conf.d/app.ini"

# Seul le dossier public/ est exposé sur le web ; Apache écoute sur 8080 (port attendu par Fly.io)
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
COPY docker/apache.conf /tmp/apache.conf
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri 's!Listen 80!Listen 8080!' /etc/apache2/ports.conf \
    && sed -ri 's!<VirtualHost \*:80>!<VirtualHost *:8080>!' /etc/apache2/sites-available/000-default.conf \
    && mv /tmp/apache.conf /etc/apache2/conf-enabled/zz-app.conf

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts
COPY . .
RUN mkdir -p public/assets/img/menus/uploads storage/mails storage/logs \
    && chown -R www-data:www-data public/assets/img/menus/uploads storage

EXPOSE 8080
