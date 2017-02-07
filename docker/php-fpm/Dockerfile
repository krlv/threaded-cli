FROM php:7.0-zts-alpine

RUN docker-php-source extract \
    && apk add --update --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install pthreads \
    && docker-php-ext-enable \
       pthreads \
    && docker-php-ext-install \
       pdo \
       pdo_mysql \
    && docker-php-source delete \
    && apk del .build-deps

CMD ["php", "./bin/console"]
