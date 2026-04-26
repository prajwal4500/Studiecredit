FROM php:8.2-apache

COPY . /var/www/html/

RUN find /var/www/html -type f -name "*.php" | head -20

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80
