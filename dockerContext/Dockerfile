FROM php:5.5-cli
RUN apt-get update && \
    apt-get install -y zlib1g-dev git && \
    rm -r /var/lib/apt/lists/*
RUN docker-php-ext-install zip
RUN curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer
RUN pecl install xdebug
RUN echo "zend_extension=/usr/local/lib/php/extensions/no-debug-non-zts-20121212/xdebug.so" > /usr/local/etc/php/conf.d/pecl-xdebug.ini
RUN echo "xdebug.remote_host="`/sbin/ip route | /usr/bin/awk '/default/ { print $3 }'` >> /usr/local/etc/php/conf.d/pecl-xdebug.ini
RUN echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/pecl-xdebug.ini && \
    echo "xdebug.remote_autostart=on" >> /usr/local/etc/php/conf.d/pecl-xdebug.ini && \
    echo "xdebug.remote_connect_back=off" >> /usr/local/etc/php/conf.d/pecl-xdebug.ini && \
    echo "xdebug.remote_handler=dbgp" >> /usr/local/etc/php/conf.d/pecl-xdebug.ini && \
    echo "xdebug.profiler_enable=0" >> /usr/local/etc/php/conf.d/pecl-xdebug.ini && \
    echo 'xdebug.profiler_output_dir="/opt/project"' >> /usr/local/etc/php/conf.d/pecl-xdebug.ini && \
    echo "xdebug.remote_port=9000" >> /usr/local/etc/php/conf.d/pecl-xdebug.ini
WORKDIR /tmp/composerCache
COPY composer.json ./
COPY composer.lock ./
RUN composer install --no-scripts --no-autoloader --prefer-dist
WORKDIR /opt/project

CMD "/usr/local/bin/php"