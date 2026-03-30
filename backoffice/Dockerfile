FROM php:8.2-apache

# Activer mod_rewrite
RUN a2enmod rewrite

# Installer les extensions PHP nécessaires
RUN docker-php-ext-install pdo pdo_mysql
