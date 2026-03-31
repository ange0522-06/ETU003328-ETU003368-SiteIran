FROM php:8.2-apache

# Activer mod_rewrite
RUN a2enmod rewrite

# Configurer le DocumentRoot vers ./public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Installer les extensions PHP nécessaires
RUN docker-php-ext-install pdo pdo_mysql
