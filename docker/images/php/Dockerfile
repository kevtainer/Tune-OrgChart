FROM php:5.6-fpm

# compile + install xdebug
RUN curl -SL "http://xdebug.org/files/xdebug-2.3.3.tgz" -o xdebug.tar.xz \
	&& mkdir -p /usr/src/xdebug \
	&& tar -xof xdebug.tar.xz -C /usr/src/xdebug --strip-components=1 \
	&& rm xdebug.tar.xz \
	&& cd /usr/src/xdebug \
	&& phpize \
	&& ./configure \
	&& make -j"$(nproc)" \
	&& cp modules/xdebug.so "$(php -r 'echo ini_get("extension_dir");')"

## RUN apt-get update && apt-get install -y mysql-client

# Install modules
RUN docker-php-ext-install mysql mysqli pdo pdo_mysql

# dumb hack to make shares writable by the server whilst docker is running on osx
RUN usermod -u 1000 www-data

CMD ["php-fpm"]
