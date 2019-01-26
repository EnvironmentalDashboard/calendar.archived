#!/bin/bash
rm /var/www/html/index.html # default apache index page
if [ -z "${COMMUNITY}" ] || [ "${COMMUNITY}" == "oberlin" ]; then
  mv '/var/www/html/includes/snippets/environmentaldashboard.org/*.php' /var/www/html/includes/snippets/
else
  mv "/var/www/html/includes/snippets/${COMMUNITY}/*.php" /var/www/html/includes/snippets/
fi
/usr/sbin/apache2ctl -D FOREGROUND
