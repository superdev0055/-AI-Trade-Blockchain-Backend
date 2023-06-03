FROM richarvey/nginx-php-fpm:3.1.4 as deps

RUN apk --no-cache add openjdk8 gmp-dev
RUN docker-php-ext-install bcmath gmp

WORKDIR /var/www/html/src
ENV WEBROOT=/var/www/html/src/public
ENV PHP_CATCHALL=1
ENV PHP_MEM_LIMIT=4096
ENV PHP_POST_MAX_SIZE=100
ENV PHP_UPLOAD_MAX_FILESIZE=100
ENV PHP_ERRORS_STDERR=1
ENV REAL_IP_HEADER=1
ENV REAL_IP_FROM=1
ENV SKIP_CHOWN=1
ENV SKIP_CHMOD=1
ENV SKIP_COMPOSER=1

COPY ./common ./common
COPY composer.json ./
RUN php -d memory_limit=-1 /usr/bin/composer install --no-dev --no-scripts --no-progress --ignore-platform-reqs

FROM deps as builder
ARG BRANCH=main

COPY ./ ./
COPY .env.${BRANCH} .env
COPY docker/nginx.conf /etc/nginx/nginx.conf

RUN mkdir -p /etc/supervisor/conf.d/
RUN cp docker/laravel-queue.conf /etc/supervisor/conf.d/

RUN cp docker/xxl-job.conf /etc/supervisor/conf.d/
RUN cp docker/application.properties docker/xxl-job-executor/BOOT-INF/classes/application.properties
RUN cd docker/xxl-job-executor && jar cvfM0 xxl-job-executor.jar .

# 运行优化脚本
RUN composer dump-autoload
RUN php artisan config:cache
RUN php artisan route:cache
RUN chmod -R 777 storage
