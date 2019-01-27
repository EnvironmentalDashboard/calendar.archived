#!/bin/bash
# timezone: https://serverfault.com/a/683651/456938
apt-get update && \
apt-get -qq -y install apt-utils tzdata apache2 php libapache2-mod-php php-mysql git && \
ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone && \
cd /var/www && \
git clone https://github.com/erusev/parsedown.git && \
git clone https://github.com/PHPMailer/PHPMailer.git && \
git clone https://github.com/neitanod/forceutf8.git && \
rm /var/www/html/index.html && \
a2enmod headers rewrite && \
if [ -z "${COMMUNITY}" ] || [ "${COMMUNITY}" == "oberlin" ]; then \
mv /var/www/html/includes/snippets/environmentaldashboard.org/*.php /var/www/html/includes/snippets/; \
else mv /var/www/html/includes/snippets/${COMMUNITY}/*.php /var/www/html/includes/snippets/; fi
# a2enmod proxy_http proxy ssl