FROM php:8.2-apache

# Activer mod_rewrite pour l'URL rewriting
RUN a2enmod rewrite

# Configurer le DocumentRoot vers la racine
ENV APACHE_DOCUMENT_ROOT /var/www/html
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Installer les extensions PHP nécessaires
RUN docker-php-ext-install pdo pdo_mysql

# Créer un fichier .htaccess par défaut
RUN echo 'RewriteEngine On\n\
RewriteCond %{SCRIPT_FILENAME} !-d\n\
RewriteCond %{SCRIPT_FILENAME} !-f\n\
RewriteRule ^articles/article-([a-z0-9-]+)-([0-9]+)\.html$ index.php?slug=$1&id=$2 [QSA,L]\n\
RewriteRule ^categorie/([a-z0-9-]+)-([0-9]+)/?$ index.php?categorie=$2 [QSA,L]\n\
RewriteRule ^([a-z0-9-]+)/?$ index.php?page=$1 [QSA,L]' > /var/www/html/.htaccess

# Permissions
RUN chmod 644 /var/www/html/.htaccess
