#!/bin/bash

if [ -z "${COMMUNITY}" ] || [ "${COMMUNITY}" = "oberlin" ]; then
  mv /var/www/html/includes/snippets/environmentaldashboard.org/*.php /var/www/html/includes/snippets/
else
  mv /var/www/html/includes/snippets/${COMMUNITY}/*.php /var/www/html/includes/snippets/
fi
mv /var/www/html/apache/http.conf /etc/apache2/sites-available/000-default.conf
ln -snf /var/www/uploads/calendar /var/www/html/images/uploads
