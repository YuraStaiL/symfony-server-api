FROM php:8.2-fpm-alpine

# Install packages
RUN apk add --no-cache curl git build-base zlib-dev oniguruma-dev autoconf bash nano
RUN apk add --update linux-headers

# Postgres
RUN apk add --no-cache libpq-dev && docker-php-ext-install pdo_pgsql

# MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Configure non-root user.
ARG PUID=1000
ARG PGID=1000
RUN apk --no-cache add shadow && \
    groupmod -o -g ${PGID} www-data && \
    usermod -o -u ${PUID} -g www-data www-data

## Nginx
#RUN apk add --update --no-cache nginx
#COPY ./docker/php-fpm/nginx.conf /etc/nginx/
#RUN chown -Rf www-data:www-data /var/lib/nginx

# Source code
RUN chown www-data:www-data /var/www
COPY --chown=www-data:www-data ./ /var/www
WORKDIR /var/www

#change user to non-root
USER www-data
#USER ${PUID}:${PGID}

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

ARG BUILD_MODE=dev
#RUN if [ ${BUILD_MODE} = dev ]; \
#    then \
#      composer install --no-interaction; \
#    else \
#      composer install --no-dev --no-interaction --no-progress --no-scripts --optimize-autoloader; \
#    fi;
#RUN chown -R www-data:www-data /var/www/vendor/

CMD bash -c "composer install && php-fpm"

EXPOSE 9000