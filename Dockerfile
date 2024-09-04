# Use uma imagem oficial do PHP como base
FROM php:8.1-fpm

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    curl \
    unzip \
    git \
    libzip-dev

# Instalar extensões do PHP necessárias
RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Definir o diretório de trabalho
WORKDIR /var/www

# Copiar os arquivos do projeto
COPY . .

# Instalar dependências do Composer
RUN composer install --no-scripts --no-autoloader

# Rodar autoload do Composer
RUN composer dump-autoload

# Dar permissões
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage

# Expor a porta 9000 e iniciar o servidor
EXPOSE 9000
CMD ["php-fpm"]
