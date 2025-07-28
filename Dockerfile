FROM php:8.2-fpm

# Installer dépendances système
RUN apt-get update && \
    apt-get install -y \
        nginx \
        supervisor \
        git \
        unzip \
        curl \
        libpq-dev \
        libyaml-dev \
        && docker-php-ext-install pdo pdo_pgsql pgsql \
        && pecl install yaml \
        && docker-php-ext-enable yaml \
        && apt-get clean && rm -rf /var/lib/apt/lists/*

# Installer Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Dossier de travail
WORKDIR /var/www/html

# Copier les fichiers
COPY . /var/www/html

# Installer les dépendances PHP
WORKDIR /var/www/html
RUN composer install --no-dev --optimize-autoloader

# Créer les répertoires nécessaires
RUN mkdir -p logs && \
    chmod 777 logs

# Fixer les permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

# Supprimer le fichier default nginx conf
RUN rm /etc/nginx/sites-enabled/default

# Copier ta configuration nginx et supervisord
COPY default.conf /etc/nginx/conf.d/default.conf
COPY supervisord.conf /etc/supervisord.conf

# Exposer le port
EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
