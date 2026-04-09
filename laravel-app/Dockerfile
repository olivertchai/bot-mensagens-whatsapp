# Usar a imagem oficial do PHP 8.3 com Apache
FROM php:8.3-apache

# Instalar as dependências do sistema necessárias para o PostgreSQL
RUN apt-get update && apt-get install -y libpq-dev

# Instalar e habilitar as extensões do PHP para o PostgreSQL
RUN docker-php-ext-install pdo pdo_pgsql

# Habilitar o mod_rewrite do Apache (necessário para as rotas do Laravel funcionarem)
RUN a2enmod rewrite