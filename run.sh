#!/bin/bash
docker build -t calendar .
if [ $(hostname -A) = "environmentaldashboard.org" ]; then
  docker run -dit -p 4000:80 -e COMMUNITY="" --name oberlin-calendar \
    -v /etc/opendkim/keys/environmentaldashboard.org/mail.private:/opendkim/mail.private \
    -v /var/www/uploads/calendar:/var/www/uploads/calendar \
    calendar
  docker run -dit -p 4001:80 -e COMMUNITY="obp" --name obp-calendar \
    -v /var/www/uploads/calendar:/var/www/uploads/calendar \
    calendar
  docker run -dit -p 4002:80 -e COMMUNITY="cleveland" --name cleveland-calendar calendar
else
  docker run -dit -p 4000:80 -e COMMUNITY="" --name test-calendar calendar
fi
