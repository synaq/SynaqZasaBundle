FROM centos:8.1.1911
RUN dnf -y update
RUN dnf -y install unzip
RUN dnf -y install https://dl.fedoraproject.org/pub/epel/epel-release-latest-8.noarch.rpm
RUN dnf -y install https://rpms.remirepo.net/enterprise/remi-release-8.rpm
RUN dnf -y module enable php:remi-7.4
RUN dnf -y install php php-cli php-common
RUN dnf -y install php-zip
RUN dnf -y install git
RUN echo "date.timezone=Africa/Johannesburg" > /etc/php.d/timezone.ini
RUN echo "memory_limit=4096M" > /etc/php.d/99-local-development-memory.ini
RUN curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer

WORKDIR /tmp/composerCache
COPY composer.json ./
COPY composer.lock ./
RUN composer install --no-scripts --no-autoloader --prefer-dist
WORKDIR /opt/project

RUN dnf -y install php-pecl-xdebug
RUN echo "xdebug.remote_host="`/sbin/ip route | /usr/bin/awk '/default/ { print $3 }'` >> /etc/php.d/99-local-development-xdebug.ini
RUN echo "xdebug.remote_enable=on" >> /etc/php.d/99-local-development-xdebug.ini && \
    echo "xdebug.remote_autostart=on" >> /etc/php.d/99-local-development-xdebug.ini && \
    echo "xdebug.remote_connect_back=off" >> /etc/php.d/99-local-development-xdebug.ini && \
    echo "xdebug.remote_handler=dbgp" >> /etc/php.d/99-local-development-xdebug.ini && \
    echo "xdebug.profiler_enable=0" >> /etc/php.d/99-local-development-xdebug.ini && \
    echo 'xdebug.profiler_output_dir="/opt/project"' >> /etc/php.d/99-local-development-xdebug.ini && \
    echo "xdebug.remote_port=9000" >> /etc/php.d/99-local-development-xdebug.ini
RUN echo "error_reporting=E_ALL" >> /etc/php.d/99-local-development-error-reporting.ini

CMD "/usr/local/bin/php"