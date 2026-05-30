FROM php:8.3-cli

# Install ekstensi PHP yang dibutuhkan GATIFIN
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Install ekstensi GD untuk image processing
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install gd \
    && rm -rf /var/lib/apt/lists/*

# Copy seluruh project
COPY . /app

WORKDIR /app

# Buat folder upload writable
RUN mkdir -p /app/assets/foto && chmod 777 /app/assets/foto

EXPOSE 8080

# Root project langsung di /app (tanpa subfolder GATIFIN)
CMD php -S 0.0.0.0:${PORT:-8080} -t /app /app/router.php
