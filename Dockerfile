FROM php:8.2-fpm

# Instalar dependencias y herramientas necesarias
RUN apt-get update && apt-get install -y \
    gnupg2 curl apt-transport-https \
    libzip-dev zip unzip git libicu-dev libonig-dev libxml2-dev unixodbc-dev libgssapi-krb5-2 nginx

# 🔧 Remover paquetes conflictivos antes de instalar msodbcsql18
RUN apt-get remove -y libodbc2 libodbcinst2 unixodbc-common || true

# 🔐 Agregar claves y repositorio de Microsoft para el driver ODBC
RUN curl https://packages.microsoft.com/keys/microsoft.asc | apt-key add - && \
    curl https://packages.microsoft.com/config/debian/11/prod.list > /etc/apt/sources.list.d/mssql-release.list && \
    apt-get update && \
    ACCEPT_EULA=Y apt-get install -y msodbcsql18

# Instalar extensiones PHP necesarias
RUN docker-php-ext-install pdo pdo_mysql zip intl mbstring xml

# Instalar extensiones PHP para SQL Server
RUN pecl install sqlsrv pdo_sqlsrv && docker-php-ext-enable sqlsrv pdo_sqlsrv

# Copiar configuración nginx
COPY nginx.conf /etc/nginx/sites-enabled/default

WORKDIR /var/www/html

# Copiar código Laravel al contenedor
COPY . .

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN composer install --no-dev --optimize-autoloader

# Ajustar permisos para storage y bootstrap/cache
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80

CMD service nginx start && php-fpm
