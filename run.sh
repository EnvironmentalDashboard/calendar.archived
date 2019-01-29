#!/bin/bash
docker build -t calendar .
server=$(hostname -A 2>/dev/null)
if [ $server == "environmentaldashboard.org" ]; then
  docker run -dit -p 4000:80 -e COMMUNITY="" --name oberlin-calendar \
    -v /etc/opendkim/keys/environmentaldashboard.org/mail.private:/opendkim/mail.private \
    -v /var/www/uploads/calendar:/var/www/uploads/calendar \
    calendar
  docker run -dit -p 4001:80 -e COMMUNITY="obp" --name obp-calendar \
    -v /var/www/uploads/calendar:/var/www/uploads/calendar \
    calendar
  docker run -dit -p 4002:80 -e COMMUNITY="cleveland" --name cleveland-calendar \
    -v /var/www/uploads/calendar:/var/www/uploads/calendar \
    calendar
else
  docker run -dit -p 4000:80 -e COMMUNITY="" --name test-calendar \
    -v $(pwd)/images:/var/www/uploads/calendar \
    calendar
fi
