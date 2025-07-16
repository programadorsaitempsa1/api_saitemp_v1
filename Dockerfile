FROM php:8.2-fpm

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    && docker-php-ext-install pdo pdo_mysql zip mbstring exif pcntl bcmath gd

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Crear directorio de la app
WORKDIR /var/www

# Copiar archivos al contenedor
COPY . .

# Instalar dependencias de Laravel
#RUN composer install --no-interaction --prefer-dist --optimize-autoloader
RUN composer update --ignore-platform-reqs --no-interaction --prefer-dist --optimize-autoloader


# Dar permisos a la carpeta de almacenamiento
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage

# Exponer puerto (opcional si usas solo nginx)
EXPOSE 9000

CMD ["php-fpm"]
