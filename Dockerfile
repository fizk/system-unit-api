FROM php:7.4.9-apache

ARG ENV

RUN apt-get update; \
    apt-get install -y --no-install-recommends \
    libzip-dev \
    unzip \
    zip \
    vim \
    git \
    autoconf g++ make openssl libssl-dev libcurl4-openssl-dev pkg-config libsasl2-dev libpcre3-dev

RUN pecl install apcu; \
    pecl install mongodb; \
    docker-php-ext-enable apcu;  \
    docker-php-ext-enable mongodb; \
    docker-php-ext-configure zip; \
    docker-php-ext-install zip; \
    docker-php-ext-install opcache; \
    apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false; \
    rm -rf /var/lib/apt/lists/*;

RUN mkdir /var/www/public

RUN echo "<VirtualHost *:80>\n \
    DocumentRoot /var/www/public\n \
    ErrorLog \${APACHE_LOG_DIR}/error.log\n \
    CustomLog \${APACHE_LOG_DIR}/access.log combined\n \
    RewriteEngine On\n \
    RewriteRule ^index\.php$ - [L]\n \
    RewriteCond %{REQUEST_FILENAME} !-f\n \
    RewriteCond %{REQUEST_FILENAME} !-d\n \
    RewriteRule . /index.php [L]\n \
    </VirtualHost>\n" > /etc/apache2/sites-available/000-default.conf

RUN a2enmod rewrite && service apache2 restart;

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN if [ "$ENV" != "production" ] ; then \
    pecl install xdebug; \
    docker-php-ext-enable xdebug; \
    echo "error_reporting = E_ALL" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; \
    echo "display_startup_errors = On" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; \
    echo "display_errors = On" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; \
    echo "xdebug.remote_enable=1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; \
    fi ;

WORKDIR /var/www

COPY ./composer.json /var/www/composer.json

RUN if [ "$ENV" != "production" ] ; then \
    composer install --prefer-source --no-interaction --no-suggest \
    && composer dump-autoload; \
    fi ;

RUN if [ "$ENV" = "production" ] ; then \
    composer install --prefer-source --no-interaction --no-dev --no-suggest -a \
    && composer dump-autoload -a; \
    fi ;

COPY ./phpunit.xml /var/www/phpunit.xml
COPY ./phpcs.xml /var/www/phpcs.xml
COPY ./public /var/www/public
COPY ./src /var/www/src
COPY ./config /var/www/config

RUN chown -R www-data /var/www
