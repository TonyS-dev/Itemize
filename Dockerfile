FROM php:8.4-fpm

WORKDIR /var/www

# 1. Instalar dependencias del sistema y Node.js 22
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip libpq-dev && \
    curl -fsSL https://deb.nodesource.com/setup_22.x | bash - && \
    apt-get install -y nodejs

# 2. Instalar extensiones PHP
RUN docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd

# 3. Copiar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 4. Copiar código
COPY . .

# 5. Instalar dependencias (PHP primero para que artisan funcione)
RUN composer install --no-dev --optimize-autoloader

# 6. Construir Frontend (Ahora sí funcionará porque tiene PHP y Node 22)
RUN npm ci
RUN npm run build

# 7. Permisos y usuario
RUN chown -R www-data:www-data /var/www
USER www-data

EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
