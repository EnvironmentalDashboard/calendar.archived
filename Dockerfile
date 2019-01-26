FROM ubuntu:18.04
ENV DEBIAN_FRONTEND=noninteractive \
    TZ=America/New_York \
    APACHE_RUN_USER=www-data \
    APACHE_RUN_GROUP=www-data \
    APACHE_LOG_DIR=/var/log/apache2 \
    APACHE_LOCK_DIR=/var/lock/apache2 \
    APACHE_PID_FILE=/var/run/apache2.pid
WORKDIR /var/www
# timezone: https://serverfault.com/a/683651/456938
RUN apt-get update && \
  apt-get -qq -y install apt-utils tzdata apache2 php libapache2-mod-php php-mysql git && \
  ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone && \
  git clone https://github.com/erusev/parsedown.git && \
  git clone https://github.com/PHPMailer/PHPMailer.git && \
  git clone https://github.com/neitanod/forceutf8.git
  #a2enmod access_compat alias auth_basic authn_core authn_file authz_core authz_host authz_user autoindex deflate dir env filter headers mime mpm_prefork negotiation php7.2 proxy_http proxy reqtimeout rewrite setenvif socache_shmcb ssl status
COPY . /var/www/html
# ADD https://github.com/EnvironmentalDashboard/includes.git /var/www/html
EXPOSE 80
CMD /var/www/html/init.sh
