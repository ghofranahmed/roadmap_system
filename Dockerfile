FROM php:8.4-fpm

# تثبيت المتطلبات
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    libonig-dev libxml2-dev libzip-dev libpq-dev libicu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring zip exif pcntl bcmath gd intl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# تثبيت Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# مجلد العمل
WORKDIR /var/www

# نسخ الملفات
COPY . .

# تثبيت dependencies
RUN composer install --no-dev --optimize-autoloader

# صلاحيات
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# expose port
EXPOSE 8080

# تشغيل Laravel
CMD php artisan serve --host=0.0.0.0 --port=8080
