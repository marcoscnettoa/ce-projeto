FROM php:8.1-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    vim \
    cron \
    procps \
    unzip && docker-php-ext-install zip

RUN  curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN docker-php-ext-install pdo_mysql mbstring gd

# Fuso Horário
RUN ln -sf /usr/share/zoneinfo/America/Sao_Paulo /etc/localtime

# Memória
RUN echo "memory_limit = -1" >> /usr/local/etc/php/php.ini
RUN echo "max_input_vars = 5000" >> /usr/local/etc/php/php.ini
RUN echo "post_max_size = 50M" >> /usr/local/etc/php/php.ini
RUN echo "upload_max_filesize = 50M" >> /usr/local/etc/php/php.ini
RUN echo "max_file_uploads = 20" >> /usr/local/etc/php/php.ini

# Crontab
RUN mkdir -p /etc/cron.d/
COPY crontab /etc/cron.d
RUN chmod 0644 /etc/cron.d/crontab
RUN crontab /etc/cron.d/crontab

WORKDIR /app
COPY composer.json .
RUN composer install --no-scripts
COPY . .

CMD php artisan serve --host=0.0.0.0 --port 80
