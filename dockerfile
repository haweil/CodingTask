FROM php:8.3-fpm

ARG user=TaskAbwab
ARG uid=1000

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && apt-get clean\
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer


RUN useradd -u ${uid} -ms /bin/bash -g www-data ${user}
COPY . /var/www

RUN chown -R ${user}:www-data /var/www

USER ${user}

WORKDIR /var/www

EXPOSE 9000
CMD ["php-fpm"]
