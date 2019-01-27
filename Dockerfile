FROM ubuntu:18.04
ENV DEBIAN_FRONTEND=noninteractive \
    TZ=America/New_York \
    APACHE_RUN_USER=www-data \
    APACHE_RUN_GROUP=www-data \
    APACHE_LOG_DIR=/var/log/apache2 \
    APACHE_LOCK_DIR=/var/lock/apache2 \
    APACHE_PID_FILE=/var/run/apache2.pid
WORKDIR /var/www
RUN apt-get update && \
  apt-get -qq -y install apt-utils tzdata apache2 php libapache2-mod-php php-mysql git && \
  ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone && \
  git clone https://github.com/erusev/parsedown.git && \
  git clone https://github.com/PHPMailer/PHPMailer.git && \
  git clone https://github.com/neitanod/forceutf8.git && \
  rm /var/www/html/index.html && \
  a2enmod headers rewrite
  # a2enmod proxy_http proxy ssl
  # timezone: https://serverfault.com/a/683651/456938
COPY . /var/www/html
# can only run this after files COPY'd over but want to take advantage of cache for prev RUN command
RUN if [ -z "${COMMUNITY}" ] || [ "${COMMUNITY}" == "oberlin" ]; then \
  mv /var/www/html/includes/snippets/environmentaldashboard.org/*.php /var/www/html/includes/snippets/; \
  else mv /var/www/html/includes/snippets/${COMMUNITY}/*.php /var/www/html/includes/snippets/; fi && \
  mv /var/www/html/apache/http.conf /etc/apache2/sites-available/000-default.conf
EXPOSE 80
CMD /usr/sbin/apache2ctl -D FOREGROUND
