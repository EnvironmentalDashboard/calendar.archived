#!/bin/bash

# Prepare a FQDN into a domain name.
# On Linux, dnsdomainname can be used,
# but using cut allows for backwards-compatibility
# with Mac OS.
production_domain=environmentaldashboard.org
domain=`cut -f 2- -d . <<< $HOSTNAME`
if [ "$domain" = "$production_domain" ] || [ "$HOSTNAME" = "$production_domain" ]; then
  docker run -dit -p 4000:80 -e COMMUNITY="" --name oberlin-calendar --restart always \
    -v /etc/opendkim/keys/environmentaldashboard.org/mail.private:/opendkim/mail.private \
    -v /var/www/uploads/calendar:/var/www/uploads/calendar \
    calendar
  docker run -dit -p 4001:80 -e COMMUNITY="obp" --name obp-calendar --restart always \
    -v /var/www/uploads/calendar:/var/www/uploads/calendar \
    calendar
  docker run -dit -p 4002:80 -e COMMUNITY="cleveland" --name cleveland-calendar --restart always \
    -v /var/www/uploads/calendar:/var/www/uploads/calendar \
    calendar
else
  docker run -dit -p 4000:80 -e COMMUNITY="" --name test-calendar \
    -v $(pwd)/images:/var/www/uploads/calendar \
    calendar
fi
