FROM php:8.3-cli

RUN docker-php-ext-install mysqli pdo pdo_mysql

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install gd \
    && rm -rf /var/lib/apt/lists/*

COPY . /app

WORKDIR /app

RUN mkdir -p /app/GATIFIN/assets/foto && chmod 777 /app/GATIFIN/assets/foto

EXPOSE 8080

CMD php -S 0.0.0.0:${PORT:-8080} -t /app/GATIFIN /app/router.php
