#!/bin/bash

if [ -z "${COMMUNITY}" ] || [ "${COMMUNITY}" = "oberlin" ]; then
  mv /var/www/html/includes/snippets/environmentaldashboard.org/*.php /var/www/html/includes/snippets/
else
  mv /var/www/html/includes/snippets/${COMMUNITY}/*.php /var/www/html/includes/snippets/
fi

mv /var/www/html/apache/http.conf /etc/apache2/sites-available/000-default.conf

ln -snf /var/www/uploads/calendar /var/www/html/images/uploads

INI_LOC=`php -i | grep 'Loaded Configuration File => ' | sed 's/Loaded Configuration File => //g' | sed 's/cli/apache2/g'`
sed -ie 's/upload_max_filesize = 2M/upload_max_filesize = 64M/g' "$INI_LOC"
sed -ie 's/post_max_size = 8M/post_max_size = 512M/g' "$INI_LOC"