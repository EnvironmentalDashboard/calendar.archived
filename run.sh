#!/bin/bash

if [ -f "db.config" ]
then
  . db.config
fi

# Prepare a FQDN into a domain name.
# On Linux, dnsdomainname can be used,
# but using cut allows for backwards-compatibility
# with Mac OS.
production_domain1="environmentaldashboard.org"
production_domain2="communityhub.cloud"
domain=`cut -f 2- -d . <<< $HOSTNAME`

if [ "$domain" = "$production_domain1" ] || [ "$HOSTNAME" = "$production_domain1" ] || \
	 [ "$domain" = "$production_domain2" ] || [ "$HOSTNAME" = "$production_domain2" ]
then
  docker run -dit -p 4000:80 -e COMMUNITY="" -e MYSQL_HOST="$host" -e MYSQL_USER="$user" -e MYSQL_PASS="$pass" -e MYSQL_DB="$db" --name oberlin-calendar --restart always \
    -v /etc/opendkim/keys/environmentaldashboard.org/mail.private:/opendkim/mail.private \
    -v /var/www/uploads/calendar:/var/www/uploads/calendar \
    calendar
  docker run -dit -p 4001:80 -e COMMUNITY="obp" -e MYSQL_HOST="$host" -e MYSQL_USER="$user" -e MYSQL_PASS="$pass" -e MYSQL_DB="$db" --name obp-calendar --restart always \
    -v /var/www/uploads/calendar:/var/www/uploads/calendar \
    calendar
  docker run -dit -p 4002:80 -e COMMUNITY="cleveland" -e MYSQL_HOST="$host" -e MYSQL_USER="$user" -e MYSQL_PASS="$pass" -e MYSQL_DB="$db" --name cleveland-calendar --restart always \
    -v /var/www/uploads/calendar:/var/www/uploads/calendar \
    calendar
  docker run -dit -p 4003:80 -e COMMUNITY="sewanee" -e MYSQL_HOST="$host" -e MYSQL_USER="$user" -e MYSQL_PASS="$pass" -e MYSQL_DB="sewanee" --name sewanee-calendar --restart always \
    -v /var/www/uploads/sewanee/calendar:/var/www/uploads/calendar \
    calendar
else
  docker run -dit -p 4000:80 -e COMMUNITY="" -e MYSQL_HOST="$host" -e MYSQL_USER="$user" -e MYSQL_PASS="$pass" -e MYSQL_DB="$db" --name test-calendar \
    -v $(pwd)/images:/var/www/uploads/calendar \
    calendar
fi
