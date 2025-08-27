FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libicu-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Node.js 20.x and NPM
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm@latest \
    && npm install -g vite

# Set working directory
WORKDIR /var/www

# Copy package files (if present)
COPY package*.json ./
COPY vite.config.js ./
COPY tailwind.config.js ./

RUN npm install

# Copy existing application directory
COPY . /var/www

RUN npm run build

# Create build directory and set permissions (only if exists)
RUN mkdir -p /var/www/public/build && \
    chown -R www-data:www-data /var/www/public/build

# Change ownership of our applications
RUN chown -R www-data:www-data /var/www

# Change current user to www-data
USER www-data 