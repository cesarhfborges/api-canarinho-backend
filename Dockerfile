FROM php:8.2-fpm

WORKDIR /var/www/html

# ============================
# Dependências do sistema
# ============================
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    procps \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    && rm -rf /var/lib/apt/lists/*

# ============================
# Extensões PHP
# ============================
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mbstring \
        zip \
        gd \
        opcache


# ============================
# Config OPCACHE
# ============================
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# ============================
# Composer (oficial)
# ============================
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

USER www-data

# ============================
# Copia o projeto
# ============================
COPY --chown=www-data:www-data . .

# ============================
# Instala dependências PHP
# ============================
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

# ============================
# Permissões (Laravel/Lumen)
# ============================
RUN mkdir -p \
    storage/logs \
    storage/framework/cache \
    storage/framework/views \
    && chown -R www-data:www-data storage \
    && chmod -R 775 storage storage/logs storage/framework/cache storage/framework/views storage/app

EXPOSE 9000

CMD ["php-fpm"]
