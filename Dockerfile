# Stage 1: Composer Install
FROM composer:2.6.5 AS builder

WORKDIR /opt/builder

COPY composer.json composer.lock ./
RUN composer install --optimize-autoloader --no-dev

# Stage 2: Final Image. Wordpress version is updated using composer
FROM wordpress:6.4.3-php8.2-apache

# Enable SSL module
# RUN a2enmod ssl

# # Copy SSL certificates and configure Apache to use SSL
# COPY ssl/tip.local.crt /etc/ssl/certs/
# COPY ssl/tip.local.key /etc/ssl/private/
# COPY ssl/apache-config.conf /etc/apache2/sites-available/wordpress-ssl.conf
# RUN a2ensite wordpress-ssl.conf

# Adjust Apache configuration for correct document root and directory path.
# For more information see https://hub.docker.com/_/wordpress (Static image / updates-via-redeploy)
RUN set -eux; \
	find /etc/apache2 -name '*.conf' -type f -exec sed -ri -e "s!/var/www/html!/opt/wordpress/html!g" -e "s!Directory /var/www/!Directory /opt/wordpress/!g" '{}' +;

# Copy from composer stage
COPY --from=builder --chown=www-data:www-data /opt/builder/. /opt/wordpress/

# Copy configs
COPY --chown=www-data:www-data ./upload.ini /usr/local/etc/php/conf.d/uploads.ini
COPY --chown=www-data:www-data config /opt/wordpress/config

WORKDIR /opt/wordpress/html

# Copy main files
COPY --chown=www-data:www-data html/. .htaccess ./

# Copy custom folder content to app
COPY --chown=www-data:www-data ./custom/. ./wpapp/

# Set permissions and ownership
RUN chmod -R 755 /opt/wordpress/html/wpapp/ \
    && mkdir -m 755 -p /opt/wordpress/html/wpapp/uploads/elementor/css \
    && chown -R www-data:www-data /opt/wordpress/html/wpapp/uploads/elementor/css \
    && chmod -R 755 /opt/wordpress/html/wpapp/uploads \
    && find /opt/wordpress/html/wpapp/uploads -type f -exec chmod 644 {} \; \
    && chown -R www-data:www-data /opt/wordpress/html/wpapp/uploads

EXPOSE 80
# EXPOSE 443 