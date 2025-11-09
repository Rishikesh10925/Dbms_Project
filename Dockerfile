FROM php:8.1-apache

# Install PDO MySQL extension and useful utilities
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        default-mysql-client \
        libzip-dev \
        zip \
        unzip \
        ca-certificates \
    && docker-php-ext-install pdo pdo_mysql mysqli \
    && rm -rf /var/lib/apt/lists/*

# Copy application source
COPY . /var/www/html/

# Copy entrypoint that adapts Apache Listen port to $PORT (Render sets $PORT)
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]